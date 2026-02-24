<?php

namespace monolitum\backend\crypto;

use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;
use Random\RandomException;

class CryptoManager extends MNode
{

    /**
     * @var SymmetricKey[]
     */
    private array $symmetricKeys = [];

    /**
     * @var AsymmetricKey[]
     */
    private array $asymmetricKeys = [];

    const DEFAULT_ASYMMETRIC_CONFIG = [ // Hardcoded
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    const DEFAULT_SYMMETRIC_ALG = "aes256";
    const DEFAULT_SYMMETRIC_IV = "0123456789abcdef";

    /**
     * @param callable|null $builder
     */
    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param string $name
     * @param AsymmetricKey $asymmetricKey
     * @return $this
     */
    public function addAsymmetricKey(string $name, AsymmetricKey $asymmetricKey): self
    {
        $this->asymmetricKeys[$name] = $asymmetricKey;
        return $this;
    }

    /**
     * @param string $name
     * @param SymmetricKey $symmetricKey
     * @return $this
     */
    public function addSymmetricKey(string $name, SymmetricKey $symmetricKey): self
    {
        $this->symmetricKeys[$name] = $symmetricKey;
        return $this;
    }

    /**
     * @param $digestAlg
     * @param $bits
     * @return AsymmetricKey
     */
    public function generateKeyPair($digestAlg = null, $bits = null): AsymmetricKey
    {
        $config = [
            "digest_alg" => ($digestAlg !== null ? $digestAlg : self::DEFAULT_ASYMMETRIC_CONFIG["digest_alg"]),
            "private_key_bits" => ($bits !== null ? $bits : self::DEFAULT_ASYMMETRIC_CONFIG["private_key_bits"]),
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

//        $data = 'plaintext data goes here';
//
//        // Encrypt the data to $encrypted using the public key
//        openssl_public_encrypt($data, $encrypted, $pubKey);
//
//        // Decrypt the data using the private key and store the results in $decrypted
//        openssl_private_decrypt($encrypted, $decrypted, $privKey);

        return AsymmetricKey::from(
            $pubKey,
            $privKey,
            $digestAlg != null || $bits !== null ? $config : null
        );

    }

    /**
     * @param string $keyname
     * @param string $data
     * @param bool $randomInitializationVector
     * @return string|null
     * @throws DevPanic|RandomException
     */
    public function encrypt(string $keyname, string $data, bool $randomInitializationVector = true): ?string
    {
        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->symmetricKeys)){
            $key = $this->symmetricKeys[$keyname];

            $algorithm = $key->getAlgorithm();
            if($algorithm === null)
                $algorithm = self::DEFAULT_SYMMETRIC_ALG;

            if($randomInitializationVector){

                $iv = random_bytes(16);
                return pack("C", strlen($iv)) . $iv . openssl_encrypt($data, $algorithm, $key->getPassword(), OPENSSL_RAW_DATA, $iv);

            }else{

                $iv = $key->getDefaultInitializationVector();
                if($iv == null)
                    $iv = self::DEFAULT_SYMMETRIC_IV;

                return pack("C", 0) . openssl_encrypt($data, $algorithm, $key->getPassword(), OPENSSL_RAW_DATA, $iv);

            }

        }else if(array_key_exists($keyname, $this->asymmetricKeys)){
            // Encrypt with my public key, so later I can decrypt it
            $key = $this->asymmetricKeys[$keyname];

            $encrypt_result = openssl_public_encrypt($data, $encrypted, $key->getPublicKey($this));

            if($encrypt_result){
                return $encrypted;
            }else{
                return null;
            }

        }else {
            throw new DevPanic("Key $keyname not found.");
        }

    }

    /**
     * @param $keyname
     * @param $data
     * @param $randomInitializationVector
     * @return string|null
     */
    public function decrypt(string $keyname, string $data): ?string
    {

        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->symmetricKeys)){
            $key = $this->symmetricKeys[$keyname];

            $algorithm = $key->getAlgorithm();
            if($algorithm === null)
                $algorithm = self::DEFAULT_SYMMETRIC_ALG;

            $lenOfIvStr = substr($data, 0, 1);
            if($lenOfIvStr === false || $lenOfIvStr === "")
                throw new DevPanic("IV not found in data.");

            $lenOfIv = unpack("C", $lenOfIvStr)[1];

            if($lenOfIv > 0){
                $iv = substr($data, 1, $lenOfIv);
                if($iv === "")
                    throw new DevPanic("IV not found in data.");
            }else{
                $iv = $key->getDefaultInitializationVector();
                if($iv === null)
                    $iv = self::DEFAULT_SYMMETRIC_IV;
            }

            $data = substr($data, $lenOfIv+1);

            $decrypt_result = openssl_decrypt($data, $algorithm, $key->getPassword(), OPENSSL_RAW_DATA, $iv);

            if($decrypt_result === false){
                return null;
            }else{
                return $decrypt_result;
            }

        }else if(array_key_exists($keyname, $this->asymmetricKeys)){
            // Decrypt with my private key

            $key = $this->asymmetricKeys[$keyname];

            $privateKey = $key->getPrivateKey($this);
            if($privateKey === null)
                throw new DevPanic("Private key $privateKey not found.");

            $decrypt_result = openssl_private_decrypt($data, $encrypted, $privateKey);

            if($decrypt_result){
                return $encrypted;
            }else{
                return null;
            }

        }else {
            throw new DevPanic("Key $keyname not found.");
        }

    }

    /**
     * Signs a data with a private key
     * @param string $keyname
     * @param string $data
     * @return string|null
     */
    public function sign(string $keyname, string $data): ?string
    {
        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->asymmetricKeys)) {

            $key = $this->asymmetricKeys[$keyname];

            $privateKey = $key->getPrivateKey($this);
            if($privateKey === null)
                throw new DevPanic("Private key $privateKey not found.");

            $encrypt_result = openssl_private_encrypt($data, $encrypted, $privateKey);

            if($encrypt_result){
                return $encrypted;
            }else{
                return null;
            }

        }else{
            throw new DevPanic("Key $keyname not found.");
        }
    }

    /**
     * Signs the hash of a data with a private key.
     * Returns or the signature, or the data with the signature appended (default).
     * @param string $keyname
     * @param string $data
     * @param bool $includeDataInOutput
     * @return string|null
     */
    public function hashSign(string $keyname, string $data, bool $includeDataInOutput=true): ?string
    {
        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->asymmetricKeys)) {

            $key = $this->asymmetricKeys[$keyname];

            $privateKey = $key->getPrivateKey($this);
            if($privateKey === null)
                throw new DevPanic("Private key $privateKey not found.");

            $encrypt_result = openssl_sign($data, $signature, $privateKey);

            if($encrypt_result){
                if($includeDataInOutput){
                    // Transform the length of signature to a single byte
                    $numberOfBytes = pack('C', strlen($signature));
                    return $data . $signature . $numberOfBytes;
                }else{
                    return $signature;
                }
            }else{
                return null;
            }

        }else{
            throw new DevPanic("Key $keyname not found.");
        }
    }

    /**
     * Decodes data with public key to verify
     * @param string $keyname
     * @param string $data
     * @return string|null
     */
    public function verify(string $keyname, string $data): ?string
    {
        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->asymmetricKeys)) {

            $key = $this->asymmetricKeys[$keyname];

            $publicKey = $key->getPublicKey($this);
            if($publicKey === null)
                throw new DevPanic("Public key $publicKey not found.");

            $decrypt_result = openssl_public_decrypt($data, $decrypted, $publicKey);

            if($decrypt_result){
                return $decrypted;
            }else{
                return null;
            }

        }else{
            throw new DevPanic("Key $keyname not found.");
        }
    }

    /**
     * Verifies the hash of a data, if signature is null, it will assume it's appended in data.
     * @param string $keyname
     * @param string $data
     * @param string|null $signature
     * @return bool|null
     */
    public function hashVerify(string $keyname, string $data, ?string $signature=null): bool|null
    {
        if($data === null)
            return null;

        if(array_key_exists($keyname, $this->asymmetricKeys)) {

            $key = $this->asymmetricKeys[$keyname];

            $privateKey = $key->getPrivateKey($this);
            if($privateKey === null)
                throw new DevPanic("Private key $privateKey not found.");

            if($signature === null){
                $dataLen = strlen($data);
                $signatureLen = unpack('C', substr($data, $dataLen-1 , 1));
                $signature = substr($data, $dataLen-1-$signatureLen, $signatureLen);
                $data = substr(0, $dataLen-1-$signatureLen);
            }

            $encrypt_result = openssl_verify($data, $signature, $privateKey);

            if($encrypt_result === 0){
                return false;
            }else if($encrypt_result === 1) {
                return true;
            }else{
                return null;
            }

        }else{
            throw new DevPanic("Key $keyname not found.");
        }
    }

    /**
     * @param int $length
     * @param bool $uc
     * @param bool $lc
     * @param bool $n
     * @param bool $sc
     * @return string
     */
    public function generateStringCode(int $length = 8, bool $uc=true, bool $lc=true, bool $n=true, bool $sc=false): string
    {
        $chars = '';
        if ($lc) {
            $chars .= "abcdefghijklmnopqrstuvwxyz";
        }

        if ($uc) {
            $chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }

        if ($n) {
            $chars .= "0123456789";
        }

        if ($sc) {
            $chars .= "!@#$%^&*()_-=+;:,.";
        }

        return substr(str_shuffle($chars), 0, $length);
    }

}
