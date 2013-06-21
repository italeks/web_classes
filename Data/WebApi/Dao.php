<?php

namespace Data\WebApi;

class Dao implements \Data\DaoInterface
{
    private $connector;
    private $skin;
    private $params = array(
        'key'      => '56A67CCA6A6C420085D63406EF69922B',
        'function' => 2, // execSprocSimple on api side
        'protect'  => 1, // escape data on api side
    );


    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
        $this->skin = \Registry::get('skin');
        $this->params['req_ref'] = getenv("COMPUTERNAME")." ".com_create_guid();
        $this->params['network'] = $this->skin->get('network');
    }

    public function getCustomerSummary($networkID, $customerID)
    {
        if (!intval($customerID)) {
            throw new \Exception('Wrong parameter $customerID');
        }

        $this->params['db'] = 'REP';
        $this->params['sproc'] = 'wMembersGetProfileAdditional';
        $this->pushParams(array('UserID' => array($customerID, 'int', 0)));

        try {
            $data = $this->connector->send($this->params);

        } catch (\Data\ConnectionException $exception) {
            throw new \Exception\DataException('WebApi error');
            \Registry::get('logger')->emergency('WebApi is unreacheble from {class} {line}', array('class' => __CLASS__, 'line' => __LINE__));

        } catch (\Data\WrongResponceFormatException $exception) {
            throw new \Exception\DataException('WebApi error');
            \Registry::get('logger')->alert('WebApi returned corrupted data {class} {line}', array('class' => __CLASS__, 'line' => __LINE__));
        }

        return $data;
    }

    public function getTeamBingoLeaderBoard($networkID, $periodID)
    {

    }

    private function pushParams(array $params)
    {
        $this->params['params'] = serialize($params);
    }


}