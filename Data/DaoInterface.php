<?php

namespace Data;

interface DaoInterface
{
	public function getCustomerSummary($networkID, $customerID);
	public function getTeamBingoLeaderBoard($networkID, $periodID);
}