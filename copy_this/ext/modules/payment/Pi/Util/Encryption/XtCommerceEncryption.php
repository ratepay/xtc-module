<?php

require_once('EncryptionAbstract.php');

class Pi_Util_Encryption_XtCommerceEncryption extends Pi_Util_Encryption_EncryptionAbstract
{
    
    /**
     * Tablename where bankdata saved
     * 
     * @var string 
     */
    protected $_tableName = 'pi_ratepay_debitdetails';
    
    /**
     * Insert query
     * 
     * @param string $insertSql 
     */
    protected function _insertBankdataToDatabase($insertSql)
    {
        xtc_db_query($insertSql);
    }

    /**
     * Retrieve selected bankdata
     * 
     * @param string $selectSql
     * @return array 
     */
    protected function _selectBankdataFromDatabase($selectSql)
    {
        $query = xtc_db_query($selectSql);
        $sqlResult = xtc_db_fetch_array($query);
        $bankdata = array (
            'userid' => $this->_convertHexToBinary($sqlResult['userid']),
            'owner' => $this->_convertHexToBinary($sqlResult['decrypt_owner']),
            'accountnumber' => $this->_convertHexToBinary($sqlResult['decrypt_accountnumber']),
            'bankcode' => $this->_convertHexToBinary($sqlResult['decrypt_bankcode']),
            'bankname' => $this->_convertHexToBinary($sqlResult['decrypt_bankname'])
        );

        return $bankdata;
    }

    /**
     * Retrieve userId
     * 
     * @param string $userSql
     * @return integer
     */
    protected function _selectUserIdFromDatabase($userSql)
    {
        $query = xtc_db_query($userSql);
        $sqlResult = xtc_db_fetch_array($query);

        return $sqlResult['userid'];
    }
}