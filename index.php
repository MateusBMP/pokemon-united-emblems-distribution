<?php declare(strict_types=1);

enum Statistic: string
{
    case HP = 'hp';
    case ATK = 'atk';
    case DEF = 'def';
    case SP_ATK = 'sp_atk';
    case SP_DEF = 'sp_def';
    case CRIT_CHANCE = 'crit_chance';
    case MV_SPEED = 'mv_speed';
    case CD_REDUCTION = 'cd_reduction';
}

enum Level: string
{
    case BRONZE = 'C';
    case SILVER = 'B';
    case GOLD = 'A';
}

enum Color: string
{
    case BLACK = 'Black';
    case BLUE = 'Blue';
    case BROWN = 'Brown';
    case GRAY = 'Gray';
    case GREEN = 'Green';
    case NAVY = 'Navy';
    case PINK = 'Pink';
    case PURPLE = 'Purple';
    case RED = 'Red';
    case WHITE = 'White';
    case YELLOW = 'Yellow';
}

class Base
{
    public function __construct(
        public string $pokemon,
        public Level $level,
        public array $colors,
        public float $hp = 0,
        public float $atk = 0,
        public float $def = 0,
        public float $sp_atk = 0,
        public float $sp_def = 0,
        public float $crit_chance = 0,
        public float $mv_speed = 0,
        public float $cd_reduction = 0,
    ) {}
}

class Gene extends Base
{
    public static function fromBase(Base $base): self
    {
        return new self(
            $base->pokemon,
            $base->level,
            $base->colors,
            $base->hp,
            $base->atk,
            $base->def,
            $base->sp_atk,
            $base->sp_def,
            $base->crit_chance,
            $base->mv_speed,
            $base->cd_reduction,
        );
    }
}

class Cromossomo
{
    public function __construct(
        public Gene $gene1,
        public Gene $gene2,
        public Gene $gene3,
        public Gene $gene4,
        public Gene $gene5,
        public Gene $gene6,
        public Gene $gene7,
        public Gene $gene8,
        public Gene $gene9,
        public Gene $gene10,
    ) {}

    public function genes(): array
    {
        return [
            $this->gene1,
            $this->gene2,
            $this->gene3,
            $this->gene4,
            $this->gene5,
            $this->gene6,
            $this->gene7,
            $this->gene8,
            $this->gene9,
            $this->gene10,
        ];
    }
}

class Memoria
{
    /** @var array[Base] $bases */
    static public array $bases = [];
    /** @var array[Statistic] $statistics_to_improve */
    static public array $statistics_to_improve = [];
    /** @var array[Color] $colors_to_improve */
    static public array $colors_to_improve = [];

    static public function addBase(Base $base): void
    {
        self::$bases[] = $base;
    }
}

function cruzamento(Cromossomo $cromossomo1, Cromossomo $cromossomo2): Cromossomo
{
    $genes = [...$cromossomo1->genes(), ...$cromossomo2->genes()];
    shuffle($genes);
    return new Cromossomo($genes[0], $genes[1], $genes[2], $genes[3], $genes[4], $genes[5], $genes[6], $genes[7], $genes[8], $genes[9]);
}

function mutacao(Cromossomo $cromossomo): Cromossomo
{
    $gene_num = random_int(1, 10);
    $base_num = random_int(0, count(Memoria::$bases) - 1);
    /** @var Base $base */
    $base = Memoria::$bases[$base_num];
    $gene = Gene::fromBase($base);
    return new Cromossomo(
        $gene_num === 1 ? $gene : $cromossomo->gene1,
        $gene_num === 2 ? $gene : $cromossomo->gene2,
        $gene_num === 3 ? $gene : $cromossomo->gene3,
        $gene_num === 4 ? $gene : $cromossomo->gene4,
        $gene_num === 5 ? $gene : $cromossomo->gene5,
        $gene_num === 6 ? $gene : $cromossomo->gene6,
        $gene_num === 7 ? $gene : $cromossomo->gene7,
        $gene_num === 8 ? $gene : $cromossomo->gene8,
        $gene_num === 9 ? $gene : $cromossomo->gene9,
        $gene_num === 10 ? $gene : $cromossomo->gene10,
    );
}

function fitness(Cromossomo $cromossomo): float
{
    $fitness = 0;
    foreach ($cromossomo->genes() as $gene) {
        if ($gene->level === Level::GOLD) {
            $fitness += 3;
        }
        else if ($gene->level === Level::SILVER) {
            $fitness += 2;
        }
        else if ($gene->level === Level::BRONZE) {
            $fitness += 1;
        }
        foreach (Memoria::$colors_to_improve as $color) {
            if (in_array($color, $gene->colors)) {
                $fitness += 1;
            }
        }
        foreach (Memoria::$statistics_to_improve as $statistic) {
            $s = (string) $statistic->value;
            if ($gene->$s > 0) {
                $fitness += 1;
            }
            else if ($gene->$s < 0) {
                $fitness -= 1;
            }
        }
    }
    return $fitness;
}

function is_valid(Cromossomo $cromossomo): bool
{
    $genes = [];
    foreach ($cromossomo->genes() as $gene) {
        $genes[$gene->pokemon] = ($genes[$gene->pokemon] ?? 0) + 1;
    }
    return count($genes) === 10;
}

function sort_by_fitness(array $populacao): array
{
    usort($populacao, function (Cromossomo $a, Cromossomo $b) {
        return fitness($b) <=> fitness($a);
    });
    return $populacao;
}

function fit_medio(array $populacao): float
{
    $fitness = 0;
    foreach ($populacao as $cromossomo) {
        $fitness += fitness($cromossomo);
    }
    return $fitness / count($populacao);
}



$data = file_get_contents('data.json');
$json = json_decode($data, true);
foreach ($json as $pokemon)
{
    $display_name = '';
    if (array_key_exists('display_name', $pokemon))
    {
        $display_name = $pokemon['display_name'];
    }
    $colors = [];
    if (array_key_exists('color1', $pokemon))
    {
        $colors[] = Color::from($pokemon['color1']);
    }
    if (array_key_exists('color2', $pokemon))
    {
        $colors[] = Color::from($pokemon['color2']);
    }
    $stats = [];
    if (array_key_exists('stats', $pokemon) && is_array($pokemon['stats'])) {
        if (array_key_exists('hp', $pokemon['stats'][0]))
        {
            $stats[Statistic::HP->value] = $pokemon['stats'][0]['hp'];
        }
        if (array_key_exists('attack', $pokemon['stats'][0]))
        {
            $stats[Statistic::ATK->value] = $pokemon['stats'][0]['attack'];
        }
        if (array_key_exists('defense', $pokemon['stats'][0]))
        {
            $stats[Statistic::DEF->value] = $pokemon['stats'][0]['defense'];
        }
        if (array_key_exists('sp_attack', $pokemon['stats'][0]))
        {
            $stats[Statistic::SP_ATK->value] = $pokemon['stats'][0]['sp_attack'];
        }
        if (array_key_exists('sp_defense', $pokemon['stats'][0]))
        {
            $stats[Statistic::SP_DEF->value] = $pokemon['stats'][0]['sp_defense'];
        }
        if (array_key_exists('crit', $pokemon['stats'][0]))
        {
            $stats[Statistic::CRIT_CHANCE->value] = $pokemon['stats'][0]['crit'];
        }
        if (array_key_exists('speed', $pokemon['stats'][0]))
        {
            $stats[Statistic::MV_SPEED->value] = $pokemon['stats'][0]['speed'];
        }
        if (array_key_exists('cdr', $pokemon['stats'][0]))
        {
            $stats[Statistic::CD_REDUCTION->value] = $pokemon['stats'][0]['cdr'];
        }
    }
    $level = Level::BRONZE;
    if (array_key_exists('grade', $pokemon))
    {
        $level = Level::from($pokemon['grade']);
    }
    Memoria::addBase(new Base(
        pokemon: $display_name,
        level: $level,
        colors: $colors,
        hp: $stats[Statistic::HP->value] ?? 0,
        atk: $stats[Statistic::ATK->value] ?? 0,
        def: $stats[Statistic::DEF->value] ?? 0,
        sp_atk: $stats[Statistic::SP_ATK->value] ?? 0,
        sp_def: $stats[Statistic::SP_DEF->value] ?? 0,
        crit_chance: $stats[Statistic::CRIT_CHANCE->value] ?? 0,
        mv_speed: $stats[Statistic::MV_SPEED->value] ?? 0,
        cd_reduction: $stats[Statistic::CD_REDUCTION->value] ?? 0));
}

Memoria::$colors_to_improve = [Color::WHITE];
Memoria::$statistics_to_improve = [Statistic::HP, Statistic::DEF];

// Sort 10 genes
$cromossomo1 = new Cromossomo(
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
    Gene::fromBase(Memoria::$bases[rand(0, count(Memoria::$bases) - 1)]),
);


$t = 0;
$populacao = [$cromossomo1];
$populacao = sort_by_fitness($populacao);
while ($t < 50) {
    $acima_da_media = array_filter($populacao, fn($cromossomo) => fitness($cromossomo) >= fit_medio($populacao));
    for ($i = 0; $i < count($acima_da_media) - 1; $i++) {
        $populacao[] = cruzamento($acima_da_media[$i], $acima_da_media[$i + 1]);
    }
    foreach ($acima_da_media as $cromossomo) {
        $populacao[] = mutacao($cromossomo);
    }
    $populacao = array_filter($populacao, 'is_valid');
    $populacao = array_unique($populacao, SORT_REGULAR);
    $populacao = sort_by_fitness($populacao);
    $populacao = array_slice($populacao, 0, 100);
    $t++;
    echo 'Generation: ' . $t . ' - Best Fitness: ' . fitness($populacao[0]) . PHP_EOL;
}

$populacao = sort_by_fitness($populacao);
echo 'Best Fitness: ' . fitness($populacao[0]) . PHP_EOL;
echo 'Colors to improve: ' . implode(', ', array_map(fn($color) => (string) $color->name, Memoria::$colors_to_improve)) . PHP_EOL;
echo 'Statistics to improve: ' . implode(', ', array_map(fn($statistic) => (string) $statistic->value, Memoria::$statistics_to_improve)) . PHP_EOL;
echo 'Emblems: ' . PHP_EOL;
$statistics = [];
foreach ($populacao[0]->genes() as $gene) {
    echo '  ' . $gene->pokemon . ' (' . (string) $gene->level->name . ') (' . implode(', ', array_map(fn($color) => (string) $color->name, $gene->colors)) . ')' . PHP_EOL;
    foreach (Statistic::cases() as $statistic) {
        $s = (string) $statistic->value;
        $statistics[$s] = ($statistics[$s] ?? 0) + $gene->$s;
    }
}

echo 'Statistics: ' . PHP_EOL;
foreach ($statistics as $statistic => $value) {
    echo '  ' . $statistic . ': ' . $value . PHP_EOL;
}
