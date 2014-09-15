<?php
class rpBankaccountInfo
{
    private $_bankAccount;

    private $_owner;

    private $_accountNumber;

    private $_bankCode;

    private $_bankName;
    
    public function getBankAccount()
    {
        return $this->_bankAccount;
    }

    public function setBankAccount($bankAccount)
    {
        $this->_bankAccount = $bankAccount;
    }

    public function getOwner()
    {
        return $this->_owner;
    }

    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }

    public function getAccountNumber()
    {
        return $this->_accountNumber;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->_accountNumber = $accountNumber;
    }

    public function getBankCode()
    {
        return $this->_bankCode;
    }

    public function setBankCode($bankCode)
    {
        $this->_bankCode = $bankCode;
    }

    public function getBankName()
    {
        return $this->_bankName;
    }

    public function setBankName($bankName)
    {
        $this->_bankName = $bankName;
    }

    public function getData()
    {
        $data = array(
            'accountNumber' => $this->_accountNumber,
            'bankAccount'   => $this->_bankAccount,
            'bankCode'      => $this->_bankCode,
            'bankName'      => $this->_bankName,
            'owner'         => $this->_owner,
        );

        return $data;
    }
}