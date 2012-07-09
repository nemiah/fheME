<?php
/**
 * Is one pem encoded certificate the signer of another?
 *
 * The PHP openssl functionality is severely limited by the lack of a stable
 * api and documentation that might as well have been encrypted itself.
 * In particular the documention on openssl_verify() never explains where
 * to get the actual signature to verify.  The isCertSigner() function below
 * will accept two PEM encoded certs as arguments and will return true if
 * one certificate was used to sign the other.  It only relies on the
 * openssl_pkey_get_public() and openssl_public_decrypt() openssl functions,
 * which should stay fairly stable.  The ASN parsing code snippets were mostly
 * borrowed from the horde project's smime.php.
 *
 * @author Mike Green <mikey at badpenguins dot com>
 * @copyright Copyright (c) 2010, Mike Green, "classified" by Rainer Furtmeier, 2011
 * @license http://opensource.org/licenses/gpl-2.0.php GPLv2
 */
class CertTest {
	
	public static $FITCertificate = "-----BEGIN CERTIFICATE-----
MIID0zCCAzygAwIBAgIBADANBgkqhkiG9w0BAQQFADCBqDELMAkGA1UEBhMCREUx
DzANBgNVBAgTBkJheWVybjEVMBMGA1UEBxMMR2VuZGVya2luZ2VuMSUwIwYDVQQK
ExxGdXJ0bWVpZXIgSGFyZC0gdW5kIFNvZnR3YXJlMQswCQYDVQQLEwJJVDEZMBcG
A1UEAxMQUmFpbmVyIEZ1cnRtZWllcjEiMCAGCSqGSIb3DQEJARYTUmFpbmVyQEZ1
cnRtZWllci5pdDAeFw0xMTExMjIxMDMyMjRaFw0xMjExMjExMDMyMjRaMIGoMQsw
CQYDVQQGEwJERTEPMA0GA1UECBMGQmF5ZXJuMRUwEwYDVQQHEwxHZW5kZXJraW5n
ZW4xJTAjBgNVBAoTHEZ1cnRtZWllciBIYXJkLSB1bmQgU29mdHdhcmUxCzAJBgNV
BAsTAklUMRkwFwYDVQQDExBSYWluZXIgRnVydG1laWVyMSIwIAYJKoZIhvcNAQkB
FhNSYWluZXJARnVydG1laWVyLml0MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKB
gQC8dxNhck+0nus2wssd6zZxaL5IILzABLMX8M/hSefJjO13krXAMzT3VT690/3q
rVfcHpbYFnNm8Mv2dFvjRl/1Do6joIa20Yep5O9JEES5ggXa8YuJacbyA0ug2Kkp
T0c79e1JX3hMGo/sK4RPXjEp/bzl2415N/KntvUP3Dp/ZwIDAQABo4IBCTCCAQUw
HQYDVR0OBBYEFAG899efZegRV+UKsyaoVM3FXnBKMIHVBgNVHSMEgc0wgcqAFAG8
99efZegRV+UKsyaoVM3FXnBKoYGupIGrMIGoMQswCQYDVQQGEwJERTEPMA0GA1UE
CBMGQmF5ZXJuMRUwEwYDVQQHEwxHZW5kZXJraW5nZW4xJTAjBgNVBAoTHEZ1cnRt
ZWllciBIYXJkLSB1bmQgU29mdHdhcmUxCzAJBgNVBAsTAklUMRkwFwYDVQQDExBS
YWluZXIgRnVydG1laWVyMSIwIAYJKoZIhvcNAQkBFhNSYWluZXJARnVydG1laWVy
Lml0ggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEEBQADgYEAdIxmYCZ+09jr
xW5A88Hq/KZF0tnQZ9ixYNvrjhNMfpRmZ8budAcEEc+PzC8Q0scjNzqPotaTc89m
wfmBvnKLHL+936sMcouHb/9AaoyVx0off8hvak9RVxcO4ymQu+qKRwsfdO5Q31xC
0es163+mhqYBiXHzAMZXEsOsa1g2Usg=
-----END CERTIFICATE-----";
	
	/**
	 * Extract signature from der encoded cert.
	 * Expects x509 der encoded certificate consisting of a section container
	 * containing 2 sections and a bitstream.  The bitstream contains the
	 * original encrypted signature, encrypted by the public key of the issuing
	 * signer.
	 * @param string $der
	 * @return string on success
	 * @return bool false on failures
	 */
	static function extractSignature($der=false) {
		if (strlen($der) < 5)
			return false;
		
		// skip container sequence
		$der = substr($der, 4);
		// now burn through two sequences and the return the final bitstream
		while (strlen($der) > 1) {
			$class = ord($der[0]);
			$classHex = dechex($class);
			switch ($class) {
				// BITSTREAM
				case 0x03:
					$len = ord($der[1]);
					$bytes = 0;
					if ($len & 0x80) {
						$bytes = $len & 0x0f;
						$len = 0;
						for ($i = 0; $i < $bytes; $i++) {
							$len = ($len << 8) | ord($der[$i + 2]);
						}
					}
					return substr($der, 3 + $bytes, $len);
					break;
				// SEQUENCE
				case 0x30:
					$len = ord($der[1]);
					$bytes = 0;
					if ($len & 0x80) {
						$bytes = $len & 0x0f;
						$len = 0;
						for ($i = 0; $i < $bytes; $i++) {
							$len = ($len << 8) | ord($der[$i + 2]);
						}
					}
					$contents = substr($der, 2 + $bytes, $len);
					$der = substr($der, 2 + $bytes + $len);
					break;
				default:
					return false;
					break;
			}
		}
		return false;
	}

	/**
	 * Get signature algorithm oid from der encoded signature data.
	 * Expects decrypted signature data from a certificate in der format.
	 * This ASN1 data should contain the following structure:
	 * SEQUENCE
	 *    SEQUENCE
	 *       OID    (signature algorithm)
	 *       NULL
	 * OCTET STRING (signature hash)
	 * @return bool false on failures
	 * @return string oid
	 */
	static function getSignatureAlgorithmOid($der=null) {
		// Validate this is the der we need...
		if (!is_string($der) or strlen($der) < 5)
			return false;
		
		$bit_seq1 = 0;
		$bit_seq2 = 2;
		$bit_oid = 4;
		if (ord($der[$bit_seq1]) !== 0x30)
			die('Invalid DER passed to getSignatureAlgorithmOid()');
		
		if (ord($der[$bit_seq2]) !== 0x30)
			die('Invalid DER passed to getSignatureAlgorithmOid()');
		
		if (ord($der[$bit_oid]) !== 0x06)
			die('Invalid DER passed to getSignatureAlgorithmOid');
		
		// strip out what we don't need and get the oid
		$der = substr($der, $bit_oid);
		// Get the oid
		$len = ord($der[1]);
		$bytes = 0;
		if ($len & 0x80) {
			$bytes = $len & 0x0f;
			$len = 0;
			for ($i = 0; $i < $bytes; $i++) {
				$len = ($len << 8) | ord($der[$i + 2]);
			}
		}
		$oid_data = substr($der, 2 + $bytes, $len);
		// Unpack the OID
		$oid = floor(ord($oid_data[0]) / 40);
		$oid .= '.' . ord($oid_data[0]) % 40;
		$value = 0;
		$i = 1;
		while ($i < strlen($oid_data)) {
			$value = $value << 7;
			$value = $value | (ord($oid_data[$i]) & 0x7f);
			if (!(ord($oid_data[$i]) & 0x80)) {
				$oid .= '.' . $value;
				$value = 0;
			}
			$i++;
		}
		return $oid;
	}

	/**
	 * Get signature hash from der encoded signature data.
	 * Expects decrypted signature data from a certificate in der format.
	 * This ASN1 data should contain the following structure:
	 * SEQUENCE
	 *    SEQUENCE
	 *       OID    (signature algorithm)
	 *       NULL
	 * OCTET STRING (signature hash)
	 * @return bool false on failures
	 * @return string hash
	 */
	static function getSignatureHash($der=null) {
		// Validate this is the der we need...
		if (!is_string($der) or strlen($der) < 5)
			return false;
		
		if (ord($der[0]) !== 0x30)
			die('Invalid DER passed to getSignatureHash()');
		
		// strip out the container sequence
		$der = substr($der, 2);
		if (ord($der[0]) !== 0x30)
			die('Invalid DER passed to getSignatureHash()');
		
		// Get the length of the first sequence so we can strip it out.
		$len = ord($der[1]);
		$bytes = 0;
		if ($len & 0x80) {
			$bytes = $len & 0x0f;
			$len = 0;
			for ($i = 0; $i < $bytes; $i++) {
				$len = ($len << 8) | ord($der[$i + 2]);
			}
		}
		$der = substr($der, 2 + $bytes + $len);
		// Now we should have an octet string
		if (ord($der[0]) !== 0x04)
			die('Invalid DER passed to getSignatureHash()');
		
		$len = ord($der[1]);
		$bytes = 0;
		if ($len & 0x80) {
			$bytes = $len & 0x0f;
			$len = 0;
			for ($i = 0; $i < $bytes; $i++) {
				$len = ($len << 8) | ord($der[$i + 2]);
			}
		}
		return bin2hex(substr($der, 2 + $bytes, $len));
	}

	/**
	 * Determine if one cert was used to sign another
	 * Note that more than one CA cert can give a positive result, some certs
	 * re-issue signing certs after having only changed the expiration dates.
	 * @param string $cert - PEM encoded cert
	 * @param string $caCert - PEM encoded cert that possibly signed $cert
	 * @return bool
	 */
	static function isCertSigner($certPem=null, $caCertPem=null) {
		if (!function_exists('openssl_pkey_get_public'))
			die('Need the openssl_pkey_get_public() function.');
		
		if (!function_exists('openssl_public_decrypt'))
			die('Need the openssl_public_decrypt() function.');
		
		if (!function_exists('hash'))
			die('Need the php hash() function.');
		
		if (empty($certPem) or empty($caCertPem))
			return false;
		
		// Convert the cert to der for feeding to extractSignature.
		$certDer = self::pemToDer($certPem);
		if (!is_string($certDer))
			return false;
			#die('invalid certPem');
		
		// Grab the encrypted signature from the der encoded cert.
		$encryptedSig = self::extractSignature($certDer);
		if (!is_string($encryptedSig))
			die('Failed to extract encrypted signature from certPem.');
		
		// Extract the public key from the ca cert, which is what has
		// been used to encrypt the signature in the cert.
		$pubKey = openssl_pkey_get_public($caCertPem);
		if ($pubKey === false)
			die('Failed to extract the public key from the ca cert.');
		
		// Attempt to decrypt the encrypted signature using the CA's public
		// key, returning the decrypted signature in $decryptedSig.  If
		// it can't be decrypted, this ca was not used to sign it for sure...
		$rc = openssl_public_decrypt($encryptedSig, $decryptedSig, $pubKey);
		if ($rc === false)
			return false;
		
		// We now have the decrypted signature, which is der encoded
		// asn1 data containing the signature algorithm and signature hash.
		// Now we need what was originally hashed by the issuer, which is
		// the original DER encoded certificate without the issuer and
		// signature information.
		$origCert = self::stripSignerAsn($certDer);
		if ($origCert === false)
			die('Failed to extract unsigned cert.');
		
		// Get the oid of the signature hash algorithm, which is required
		// to generate our own hash of the original cert.  This hash is
		// what will be compared to the issuers hash.
		$oid = self::getSignatureAlgorithmOid($decryptedSig);
		if ($oid === false)
			die('Failed to determine the signature algorithm.');
		
		switch ($oid) {
			case '1.2.840.113549.2.2': $algo = 'md2';
				break;
			case '1.2.840.113549.2.4': $algo = 'md4';
				break;
			case '1.2.840.113549.2.5': $algo = 'md5';
				break;
			case '1.3.14.3.2.18': $algo = 'sha';
				break;
			case '1.3.14.3.2.26': $algo = 'sha1';
				break;
			case '2.16.840.1.101.3.4.2.1': $algo = 'sha256';
				break;
			case '2.16.840.1.101.3.4.2.2': $algo = 'sha384';
				break;
			case '2.16.840.1.101.3.4.2.3': $algo = 'sha512';
				break;
			default:
				die('Unknown signature hash algorithm oid: ' . $oid);
				break;
		}
		// Get the issuer generated hash from the decrypted signature.
		$decryptedHash = self::getSignatureHash($decryptedSig);
		// Ok, hash the original unsigned cert with the same algorithm
		// and if it matches $decryptedHash we have a winner.
		$certHash = hash($algo, $origCert);
		return ($decryptedHash === $certHash);
	}

	/**
	 * Convert pem encoded certificate to DER encoding
	 * @return string $derEncoded on success
	 * @return bool false on failures
	 */
	static function pemToDer($pem=null) {
		if (!is_string($pem))
			return false;
		
		$cert_split = preg_split('/(-----((BEGIN)|(END)) CERTIFICATE-----)/', $pem);
		if (!isset($cert_split[1]))
			return false;
		
		return base64_decode($cert_split[1]);
	}

	/**
	 * Obtain der cert with issuer and signature sections stripped.
	 * @param string $der - der encoded certificate
	 * @return string $der on success
	 * @return bool false on failures.
	 */
	static function stripSignerAsn($der=null) {
		if (!is_string($der) or strlen($der) < 8)
			return false;
		
		$bit = 4;
		$len = ord($der[($bit + 1)]);
		$bytes = 0;
		if ($len & 0x80) {
			$bytes = $len & 0x0f;
			$len = 0;
			for ($i = 0; $i < $bytes; $i++)
				$len = ($len << 8) | ord($der[$bit + $i + 2]);
			
		}
		return substr($der, 4, $len + 4);
	}

}

?>
