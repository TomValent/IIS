<?php

class Tournament implements ArrayAccess  {

	public mixed $data;
	public array $rounds;
	public array $results;

	public function __construct($id)
	{
		$this->data = Database::getInstance()->getTournamentByID($id);
		if (!$this->data) {
			return;
		}

		if ($this->isTeam()) {
			$matches = Database::getInstance()->getTeamMatches($id);
		}
		else {
			$matches = Database::getInstance()->getMemberMatches($id);
		}

		$rounds = [];
		$results = [];
		foreach ($matches as $m) {
			$r = $m['Round'];
			$rounds[$r][] = $m;
		}

		ksort($rounds);
		foreach ($rounds as $i => &$matches) {

			$all_results = true;
			foreach ($matches as &$m) {
				$winner = NULL;
				if ($this->isTeam()) {
					$winner_id = $m['WinnerTeamID'];
					if ($winner_id === $m['Team1ID']) {
						$winner = 0;
					} else if (!$m['isBye'] && $winner_id === $m['Team2ID']) {
						$winner = 1;
					}
				} else {
					$winner_id = $m['WinnerMemberID'];
					if ($winner_id === $m['Member1ID']) {
						$winner = 0;
					} else if (!$m['isBye'] && $winner_id === $m['Member2ID']) {
						$winner = 1;
					}
				}
				if ($winner === NULL) {
					// match has no winner yet
					$all_results = false;
				}
				$m['winner'] = $winner;
				$m['Name'][0] = Tournament::displayName($m, 0);
				$m['Name'][1] = Tournament::displayName($m, 1);
			}

			$results[$i] = $all_results;
		}
		$this->rounds = $rounds;
		$this->results = $results;
	}

	public static function displayName($match, $index) : string
	{
		if ($match['isBye'] && $index == 1) {
			return "BYE";
		}
		return displayName($match['Name'][$index]);
	}

	public static function winnerAttrib($match, $index) : string
	{
		if ($match['winner'] === $index) {
			return ' match-winner';
		}
		return '';
	}

	public function exists(): bool
	{
		return $this->data !== false;
	}

	public function roundHasResults($round): bool
	{
		if (!array_key_exists($round, $this->results)) {
			return false;
		}
		return $this->results[$round];
	}

	public function finalRoundComplete(): bool
	{
		$round = $this['ActualRound'];
		if (!array_key_exists($round, $this->results) || !array_key_exists($round, $this->rounds)) {
			return false;
		}
		return $this->results[$round] && count($this->rounds[$round]) == 1;
	}

	public function offsetSet($offset, $value) : void
	{
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}

	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset) : void
	{
		unset($this->data[$offset]);
	}

	public function offsetGet($offset) : mixed
	{
		return $this->data[$offset] ?? null;
	}

	public function isApproved(): bool
	{
		return $this['ApprovalState'] == 'approved';
	}

	public function isOngoing(): bool
	{
		return $this['ProgressState'] == 'ongoing';
	}

	public function isTeam(): bool
	{
		return $this['type'] == 'team';
	}

	public function isMember(): bool
	{
		return $this['type'] == 'member';
	}

}

function printParicipantsHTML() {

}