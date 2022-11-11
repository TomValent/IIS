## Quick zhrnutie coding standardov nech sa vyhneme merge conflictom

## Zhrnutie:
- triedy, metódy aj funkcie majú otváraciu zátvorku na novom riadku
- funkciám špecifikujeme return value (aj void)
- ak má funkcia viac návratových hodnôt vypisujú sa všetky. V príklad hore je `?string`
  ekvivalenta k `string|null`. Dá sa tam prísať všeličo napr. `string|array|MyObj` naraz.
  PHP storm vie poradiť. PHP 8.0+ toto nevynucuje, ale hádže chybu, ak sa to nedodrží.
  Nevzniknú zbytočné chyby.
- metódam a atribútom pridávame viditeľnosť
- pred a za zátvorkou `if statementu` je medzera, okolo keywordu `else` tiež
- pred if, switch, return, try, catch... dávame prázdny riadok pre prehladnosť (kludne aj za).
- setter vždy vracia danú triedu, vieš potom pekne chainovat settere za sebou
- ak voláš chainuješ viac setterov alebo iných metód a riadok je moc dlhý,
  odsadzuj na nový riadok pod seba
- v súboroch kde sa kombinuje html a php, odsadzovať php tag ako ostatné tagy,
  kód v nich ešte o jedno. V php classe ako v príklade triedy dole
- 1 prázdny riadok za poslednou zátvorkou/tagom
- Doxygen komentáre (na dohode), za mna stačí typovanie a pridávanie return typu
- Nastavit v php storme v pravo dole používanie tabu o velkosti 4 medzer

html + php odsadenie
```html
        <div class="authenticate">
    <div class="button_container">
        <button><a href="register.php">Create new account</a></button>
        <button><a href="host.php">Continue as host</a></button>
    </div>
</div>

<?php
            require '../IIS-project/src/database.php';

            try {
                $pdo = createDB();

                $q = $pdo->query('SELECT * FROM Member');
$data = $q->fetchAll(PDO::FETCH_ASSOC)

} catch (PDOException $e) {
echo "Connection error: ".$e->getMessage();
exit;
}
?>
</body>
</html>

```

Ukážková trieda:
```php
<?php

class MyObj
{

	private int $var1;
	private string $var2;

	public function __construct(int $var1, string $var2)
	{
		$this->var1 = $var1;
		$this->var2 = $var2;
	}

	public function getVar1(): int
	{
		return $this->var1;
	}
	
	public function getVar2(): string
	{
		return $this->var2;
	}
	
	public function setVar1(int $var1): MyObj
	{
		$this->var1 = $var1;

		return $this;
	}

	public function setVar2(string $var2): MyObj
	{
		$this->var2 = $var2;

		return $this;
	}

	/**
	 * @return string|null
	 */
	private function test(): ?string
	{
		$test = new MyObj(0, "");
				
		$test->setVar1(1)
			->setVar2("hello")
			->setVar1(2)
			->setVar2("there");
			
		$test->validate();
		
		if ($test->getVar1() > 0) {
			return $test->getVar2();
		} else {
			return null;
		}
}

	private function validate(): void
	{
		return;
	}
}
```