<?php

namespace App\Services;

/**
 * Lightweight WebAuthn service — handles challenge generation,
 * attestation parsing, and assertion verification without external packages.
 */
class WebAuthnService
{
    /**
     * Generate registration options (PublicKeyCredentialCreationOptions).
     */
    public function getRegistrationOptions($user, string $rpId, string $rpName): array
    {
        $challenge = random_bytes(32);

        session(['webauthn_challenge' => base64_encode($challenge)]);

        $options = [
            'rp' => [
                'name' => $rpName,
                'id'   => $rpId,
            ],
            'user' => [
                'id'          => $this->base64url_encode(strval($user->id)),
                'name'        => $user->email,
                'displayName' => $user->name,
            ],
            'challenge'             => $this->base64url_encode($challenge),
            'pubKeyCredParams'      => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257],  // RS256
            ],
            'timeout'               => 60000,
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform', // Use built-in scanner
                'userVerification'        => 'required',
                'residentKey'             => 'discouraged',
            ],
            'attestation' => 'none',
        ];

        // Exclude already-registered credentials
        $excludeCredentials = [];
        foreach ($user->webauthnCredentials as $cred) {
            $excludeCredentials[] = [
                'type' => 'public-key',
                'id'   => $cred->credential_id,
            ];
        }
        if (!empty($excludeCredentials)) {
            $options['excludeCredentials'] = $excludeCredentials;
        }

        return $options;
    }

    /**
     * Verify registration response and extract credential data.
     * Returns ['credential_id' => ..., 'public_key' => ..., 'sign_count' => ...]
     */
    public function verifyRegistration(array $data, string $rpId): array
    {
        // 1. Verify challenge
        $storedChallenge = session('webauthn_challenge');
        session()->forget('webauthn_challenge');
        if (!$storedChallenge) {
            throw new \Exception('No challenge found in session.');
        }

        // 2. Decode clientDataJSON
        $clientDataJSON = $this->base64url_decode($data['response']['clientDataJSON']);
        $clientData = json_decode($clientDataJSON, true);

        if ($clientData['type'] !== 'webauthn.create') {
            throw new \Exception('Invalid client data type.');
        }

        // Verify challenge matches
        if ($clientData['challenge'] !== $this->base64url_encode(base64_decode($storedChallenge))) {
            throw new \Exception('Challenge mismatch.');
        }

        // 3. Decode attestationObject (CBOR)
        $attestationObject = $this->base64url_decode($data['response']['attestationObject']);
        $attestation = $this->cborDecode($attestationObject);

        if (!isset($attestation['authData'])) {
            throw new \Exception('Missing authData in attestation.');
        }

        $authData = $attestation['authData'];

        // 4. Parse authData
        // rpIdHash (32) + flags (1) + signCount (4) = 37 bytes header
        $rpIdHash  = substr($authData, 0, 32);
        $flags     = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        // Verify rpIdHash
        if ($rpIdHash !== hash('sha256', $rpId, true)) {
            throw new \Exception('RP ID hash mismatch.');
        }

        // flags bit 6 = attested credential data present
        if (!($flags & 0x40)) {
            throw new \Exception('No attested credential data in authData.');
        }

        // 5. Parse attested credential data (after 37 byte header)
        $offset = 37;
        $aaguid = substr($authData, $offset, 16);
        $offset += 16;

        $credIdLen = unpack('n', substr($authData, $offset, 2))[1];
        $offset += 2;

        $credentialId = substr($authData, $offset, $credIdLen);
        $offset += $credIdLen;

        // 6. Parse COSE public key (CBOR-encoded, remainder of authData)
        $coseKeyBytes = substr($authData, $offset);
        $coseKey = $this->cborDecode($coseKeyBytes);

        // 7. Convert COSE key to PEM
        $pem = $this->coseKeyToPem($coseKey);

        return [
            'credential_id' => $this->base64url_encode($credentialId),
            'public_key'    => $pem,
            'sign_count'    => $signCount,
        ];
    }

    /**
     * Generate authentication options.
     */
    public function getAuthenticationOptions($user, string $rpId): array
    {
        $challenge = random_bytes(32);
        session(['webauthn_challenge' => base64_encode($challenge)]);

        $allowCredentials = [];
        foreach ($user->webauthnCredentials as $cred) {
            $allowCredentials[] = [
                'type' => 'public-key',
                'id'   => $cred->credential_id,
            ];
        }

        return [
            'challenge'        => $this->base64url_encode($challenge),
            'rpId'             => $rpId,
            'timeout'          => 60000,
            'userVerification' => 'required',
            'allowCredentials' => $allowCredentials,
        ];
    }

    /**
     * Verify authentication response.
     * Returns ['credential_id' => ..., 'new_sign_count' => ...]
     */
    public function verifyAuthentication(array $data, $credential, string $rpId): array
    {
        // 1. Verify challenge
        $storedChallenge = session('webauthn_challenge');
        session()->forget('webauthn_challenge');
        if (!$storedChallenge) {
            throw new \Exception('No challenge found in session.');
        }

        // 2. Decode and verify clientDataJSON
        $clientDataJSON = $this->base64url_decode($data['response']['clientDataJSON']);
        $clientData = json_decode($clientDataJSON, true);

        if ($clientData['type'] !== 'webauthn.get') {
            throw new \Exception('Invalid client data type.');
        }

        if ($clientData['challenge'] !== $this->base64url_encode(base64_decode($storedChallenge))) {
            throw new \Exception('Challenge mismatch.');
        }

        // 3. Decode authenticatorData
        $authData  = $this->base64url_decode($data['response']['authenticatorData']);
        $signature = $this->base64url_decode($data['response']['signature']);

        // Verify rpIdHash
        $rpIdHash = substr($authData, 0, 32);
        if ($rpIdHash !== hash('sha256', $rpId, true)) {
            throw new \Exception('RP ID hash mismatch.');
        }

        // flags
        $flags = ord($authData[32]);
        // bit 0 = user present
        if (!($flags & 0x01)) {
            throw new \Exception('User not present.');
        }

        $signCount = unpack('N', substr($authData, 33, 4))[1];

        // 4. Verify signature
        // signedData = authData + SHA-256(clientDataJSON)
        $clientDataHash = hash('sha256', $clientDataJSON, true);
        $signedData     = $authData . $clientDataHash;

        $pem = $credential->public_key;
        $pubKeyResource = openssl_pkey_get_public($pem);
        if (!$pubKeyResource) {
            throw new \Exception('Invalid public key.');
        }

        // Determine algorithm from key type
        $keyDetails = openssl_pkey_get_details($pubKeyResource);
        $algo = OPENSSL_ALGO_SHA256;

        // For EC keys, convert signature from WebAuthn DER format
        $verified = openssl_verify($signedData, $signature, $pubKeyResource, $algo);

        if ($verified !== 1) {
            throw new \Exception('Signature verification failed.');
        }

        // 5. Verify sign count (replay protection)
        if ($signCount > 0 && $signCount <= $credential->sign_count) {
            throw new \Exception('Sign count regression detected — possible cloned authenticator.');
        }

        return [
            'credential_id'  => $credential->credential_id,
            'new_sign_count' => $signCount,
        ];
    }

    /**
     * Convert COSE key map to PEM-encoded public key.
     */
    private function coseKeyToPem(array $coseKey): string
    {
        // kty = 2 (EC2), alg = -7 (ES256), crv = 1 (P-256)
        $kty = $coseKey[1] ?? null;

        if ($kty == 2) {
            // EC2 key
            $x = $coseKey[-2];
            $y = $coseKey[-3];

            // Build uncompressed EC point: 0x04 + x + y
            $point = "\x04" . $x . $y;

            // Wrap in ASN.1 SubjectPublicKeyInfo for P-256
            $der = $this->wrapEc2PublicKey($point);

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($der), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        if ($kty == 3) {
            // RSA key
            $n = $coseKey[-1]; // modulus
            $e = $coseKey[-2]; // exponent

            $der = $this->wrapRsaPublicKey($n, $e);

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($der), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        throw new \Exception("Unsupported key type: {$kty}");
    }

    /**
     * Wrap EC2 uncompressed point in SubjectPublicKeyInfo DER.
     */
    private function wrapEc2PublicKey(string $point): string
    {
        // OID for P-256: 1.2.840.10045.3.1.7
        // OID for EC public key: 1.2.840.10045.2.1
        $ecPublicKeyOid = hex2bin('06072a8648ce3d0201');
        $p256Oid        = hex2bin('06082a8648ce3d030107');

        $algorithmIdentifier = $this->asn1Sequence($ecPublicKeyOid . $p256Oid);
        $bitString = "\x03" . $this->asn1Length(strlen($point) + 1) . "\x00" . $point;

        return $this->asn1Sequence($algorithmIdentifier . $bitString);
    }

    /**
     * Wrap RSA modulus + exponent in SubjectPublicKeyInfo DER.
     */
    private function wrapRsaPublicKey(string $n, string $e): string
    {
        $modulus  = $this->asn1UnsignedInteger($n);
        $exponent = $this->asn1UnsignedInteger($e);

        $rsaPublicKey = $this->asn1Sequence($modulus . $exponent);
        $bitString    = "\x03" . $this->asn1Length(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;

        // OID for rsaEncryption: 1.2.840.113549.1.1.1
        $rsaOid = hex2bin('06092a864886f70d010101');
        $nullParam = hex2bin('0500');
        $algorithmIdentifier = $this->asn1Sequence($rsaOid . $nullParam);

        return $this->asn1Sequence($algorithmIdentifier . $bitString);
    }

    private function asn1Sequence(string $data): string
    {
        return "\x30" . $this->asn1Length(strlen($data)) . $data;
    }

    private function asn1UnsignedInteger(string $data): string
    {
        // Prepend 0x00 if high bit is set (to keep it positive)
        if (ord($data[0]) & 0x80) {
            $data = "\x00" . $data;
        }
        return "\x02" . $this->asn1Length(strlen($data)) . $data;
    }

    private function asn1Length(int $len): string
    {
        if ($len < 128) return chr($len);
        if ($len < 256) return "\x81" . chr($len);
        return "\x82" . pack('n', $len);
    }

    // ── Minimal CBOR Decoder ──────────────────────────────────────────

    /**
     * Decode a CBOR-encoded byte string into PHP data.
     */
    public function cborDecode(string $data)
    {
        $offset = 0;
        return $this->cborDecodeItem($data, $offset);
    }

    private function cborDecodeItem(string $data, int &$offset)
    {
        if ($offset >= strlen($data)) {
            throw new \Exception('CBOR: unexpected end of data');
        }

        $byte = ord($data[$offset]);
        $majorType = ($byte >> 5) & 0x07;
        $additionalInfo = $byte & 0x1f;
        $offset++;

        $value = $this->cborDecodeLength($data, $offset, $additionalInfo);

        switch ($majorType) {
            case 0: // unsigned int
                return $value;

            case 1: // negative int
                return -1 - $value;

            case 2: // byte string
                $bytes = substr($data, $offset, $value);
                $offset += $value;
                return $bytes;

            case 3: // text string
                $text = substr($data, $offset, $value);
                $offset += $value;
                return $text;

            case 4: // array
                $arr = [];
                for ($i = 0; $i < $value; $i++) {
                    $arr[] = $this->cborDecodeItem($data, $offset);
                }
                return $arr;

            case 5: // map
                $map = [];
                for ($i = 0; $i < $value; $i++) {
                    $key = $this->cborDecodeItem($data, $offset);
                    $val = $this->cborDecodeItem($data, $offset);
                    $map[$key] = $val;
                }
                return $map;

            case 7: // simple/float
                return $value;

            default:
                throw new \Exception("CBOR: unsupported major type {$majorType}");
        }
    }

    private function cborDecodeLength(string $data, int &$offset, int $additionalInfo): int
    {
        if ($additionalInfo < 24) return $additionalInfo;
        if ($additionalInfo === 24) { $val = ord($data[$offset]); $offset++; return $val; }
        if ($additionalInfo === 25) { $val = unpack('n', substr($data, $offset, 2))[1]; $offset += 2; return $val; }
        if ($additionalInfo === 26) { $val = unpack('N', substr($data, $offset, 4))[1]; $offset += 4; return $val; }
        if ($additionalInfo === 27) {
            $hi = unpack('N', substr($data, $offset, 4))[1];
            $lo = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
            return ($hi << 32) | $lo;
        }
        return $additionalInfo;
    }

    // ── Base64url helpers ─────────────────────────────────────────────

    public function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64url_decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
