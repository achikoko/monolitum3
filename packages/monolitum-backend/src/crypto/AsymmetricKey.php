<?php

namespace monolitum\backend\crypto;

use Closure;

class AsymmetricKey
{

//    private string $hashAlgorithm;

    public function __construct(
        private Closure|string $publicKey,
        private Closure|string|null $privateKey = null,
        public readonly mixed $config = null
    ){

    }

    /**
     * @param CryptoManager $manager
     * @return Closure|string|null
     */
    public function getPrivateKey(CryptoManager $manager): Closure|string|null
    {
        if($this->privateKey !== null){
            if(is_callable($this->privateKey)){
                $callable = $this->privateKey;
                $this->privateKey = $callable($manager);
            }
        }
        return $this->privateKey;
    }

    /**
     * @param CryptoManager $manager
     * @return Closure|string|null
     */
    public function getPublicKey(CryptoManager $manager): Closure|string|null
    {
        if($this->publicKey !== null){
            if(is_callable($this->publicKey)){
                $callable = $this->publicKey;
                $this->publicKey = $callable($manager);
            }
        }
        return $this->publicKey;
    }

}
