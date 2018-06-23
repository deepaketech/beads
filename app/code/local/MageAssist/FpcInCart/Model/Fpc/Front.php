<?php

class MageAssist_FpcInCart_Model_Fpc_Front extends Amasty_Fpc_Model_Fpc_Front
{
    /**
     * @inheritdoc
     */
    public function __construct($args = false)
    {
        parent::__construct($args);

        $this->_fillUrlInfo();
    }

    /**
     * @return void
     */
    protected function _fillUrlInfo()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $select = $adapter->select()
            ->from($resource->getTableName('core/config_data'), ['scope', 'scope_id', 'value',])
            ->where('path IN (?)', ['web/unsecure/base_url', 'web/secure/base_url',]);

        foreach ($adapter->fetchAll($select, \PDO::FETCH_ASSOC) as $row) {
            $_path = trim(parse_url($row['value'], PHP_URL_PATH), '/');
            if (true === empty($_path)) {
                continue ;
            }

            if (0 === strpos($this->_urlInfo['page'], $_path)) {
                $this->_urlInfo['page'] = trim(
                    substr($this->_urlInfo['page'], strlen($_path)),
                    '/'
                );
                break ;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function fetch()
    {
        $content = parent::fetch();

        if (true === is_string($content) && false === empty($content)) {
            /** @var MageAssist_FpcInCart_Model_Replace $model */
            $model = new MageAssist_FpcInCart_Model_Replace();
            return $model->replaceCache($content, $this->isAjax());
        }

        return $content;
    }
}
