# PHP 8.4 — Référence Complète pour IDE IA

> Document de référence exhaustif couvrant PHP 8.4 : nouvelles fonctionnalités, syntaxe, types, fonctions natives, et bonnes pratiques.

---

## Nouveautés PHP 8.4

### Property Hooks (RFC)

PHP 8.4 introduit les **property hooks**, permettant de définir une logique `get`/`set` directement sur une propriété.

```php
class User {
    public string $name {
        get => strtoupper($this->name);
        set(string $value) {
            if (strlen($value) < 2) throw new \ValueError("Name too short");
            $this->name = $value;
        }
    }
}

$u = new User();
$u->name = "alice";
echo $u->name; // ALICE
```

**Règles :**
- `get` : exécuté à la lecture de la propriété.
- `set` : exécuté à l'écriture. Reçoit la valeur via `$value` (paramètre typé).
- Une propriété avec hook `get` uniquement est **virtuelle** (pas de stockage).
- Les hooks sont héritables et peuvent être `abstract` dans les interfaces.

---

### Asymmetric Visibility (RFC)

Contrôle séparé de la visibilité en lecture et en écriture.

```php
class Post {
    public private(set) string $title;
    public protected(set) int $views = 0;

    public function __construct(string $title) {
        $this->title = $title;
    }

    public function incrementViews(): void {
        $this->views++;
    }
}

$p = new Post("Hello");
echo $p->title;   // OK — lecture publique
$p->title = "X";  // Error — écriture privée
```

**Syntaxe :** `visibilité_lecture visibilité_écriture(set) type $prop`  
Valeurs possibles : `public`, `protected`, `private`.

---

### `#[\Deprecated]` Attribute

Marquer du code comme déprécié directement en PHP (sans doc-block).

```php
#[\Deprecated("Use newMethod() instead", since: "8.4")]
function oldFunction(): void {}

oldFunction(); // Déclenche E_USER_DEPRECATED
```

---

### `array_find()`, `array_find_key()`, `array_any()`, `array_all()`

```php
$data = [3, 7, 12, 5];

// Premier élément satisfaisant le callback
$found = array_find($data, fn($n) => $n > 10); // 12

// Clé du premier élément satisfaisant
$key = array_find_key($data, fn($n) => $n > 10); // 2

// Vrai si AU MOINS UN élément satisfait
$any = array_any($data, fn($n) => $n > 10); // true

// Vrai si TOUS les éléments satisfont
$all = array_all($data, fn($n) => $n > 3); // false
```

---

### `new` en initialisateurs sans parenthèses

```php
// PHP 8.4 : parenthèses optionnelles sur new dans certains contextes
$obj = new MyClass->method();   // chaînage direct
$val = new MyClass::CONST;      // accès constant direct
```

---

### `#[Attribute]` sur les constantes de classe

```php
class Config {
    #[Sensitive]
    const API_KEY = "secret";
}
```

---

### `round()` — mode d'arrondi

```php
round(2.5, mode: PHP_ROUND_HALF_UP);    // 3
round(2.5, mode: PHP_ROUND_HALF_DOWN);  // 2
round(2.5, mode: PHP_ROUND_HALF_EVEN);  // 2
round(2.5, mode: PHP_ROUND_HALF_ODD);   // 3
```

---

## Types & Système de Types

### Types scalaires et primitifs

| Type | Description |
|------|-------------|
| `int` | Entier (64-bit sur 64-bit OS) |
| `float` | Flottant double précision |
| `string` | Chaîne de bytes |
| `bool` | `true` / `false` |
| `null` | Valeur nulle |

### Types composites

```php
// Union types (8.0+)
function foo(int|string $x): void {}

// Intersection types (8.1+) — toutes les interfaces doivent être satisfaites
function bar(Countable&Iterator $x): void {}

// DNF types (8.2+) — combinaison union + intersection
function baz((Countable&Iterator)|null $x): void {}

// never (8.1+) — fonction qui ne retourne jamais
function fail(string $msg): never {
    throw new \RuntimeException($msg);
}

// void — ne retourne rien (peut retourner implicitement)
function log(string $msg): void {}
```

### Types spéciaux

```php
// mixed — accepte tout
function anything(mixed $x): mixed {}

// self, static, parent
class A {
    public function clone(): static { return new static(); }
}

// Nullable shorthand
function maybe(?string $x): ?int {}
// Équivalent à : string|null et int|null
```

### Readonly

```php
// Propriétés readonly (8.1+)
class Point {
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}
}

// Classes readonly (8.2+) — toutes les propriétés implicitement readonly
readonly class Vector {
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}
```

### Enums (8.1+)

```php
// Enum pur
enum Direction {
    case North;
    case South;
    case East;
    case West;
}

// Enum backed (int ou string)
enum Status: string {
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string {
        return match($this) {
            Status::Active   => 'Actif',
            Status::Inactive => 'Inactif',
        };
    }
}

Status::from('active');       // Status::Active
Status::tryFrom('unknown');   // null
Status::Active->value;        // 'active'
Status::cases();              // [Status::Active, Status::Inactive]
```

---

## Fibers (8.1+)

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend("first");
    echo "Got: $value\n";
});

$v1 = $fiber->start();    // "first"
$fiber->resume("hello");  // "Got: hello"
```

---

## Attributs (8.0+)

```php
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route {
    public function __construct(
        public readonly string $path,
        public readonly string $method = 'GET',
    ) {}
}

#[Route('/users', 'GET')]
class UserController {
    #[Route('/users/{id}', 'GET')]
    public function show(int $id): void {}
}

// Lecture des attributs via Reflection
$ref = new ReflectionClass(UserController::class);
foreach ($ref->getAttributes(Route::class) as $attr) {
    $route = $attr->newInstance(); // Route('/users', 'GET')
}
```

---

## Named Arguments (8.0+)

```php
function create(string $name, int $age = 0, bool $active = true): array {
    return compact('name', 'age', 'active');
}

create(name: 'Alice', active: false, age: 30);
// L'ordre n'a pas d'importance avec les arguments nommés

// Utile avec les fonctions natives
array_slice(array: $arr, offset: 2, length: 5, preserve_keys: true);
htmlspecialchars(string: $html, encoding: 'UTF-8');
```

---

## Match Expression (8.0+)

```php
$status = 2;

$label = match($status) {
    1       => "Pending",
    2, 3    => "Processing",
    4       => "Done",
    default => "Unknown",
};

// Comparaison stricte (===), pas de fallthrough
// Lance UnhandledMatchError si aucun cas ne correspond et pas de default
```

---

## Nullsafe Operator (8.0+)

```php
$city = $user?->getAddress()?->getCity()?->name;
// Retourne null si n'importe quel maillon est null, sans exception
```

---

## First Class Callables (8.1+)

```php
$fn = strlen(...);         // Closure depuis fonction native
$fn = $obj->method(...);   // Closure depuis méthode d'instance
$fn = Cls::staticMethod(...); // Closure depuis méthode statique

$lengths = array_map(strlen(...), ['foo', 'hello']); // [3, 5]
```

---

## Intersection Types (8.1+) & DNF (8.2+)

```php
// Intersection : doit implémenter TOUTES les interfaces
function process(Countable&Stringable $x): void {}

// DNF = Disjunctive Normal Form : (A&B)|C
function handle((Countable&Iterator)|null $x): void {}
```

---

## Fonctions Importantes — Référence

### Chaînes de caractères

```php
str_contains(string $haystack, string $needle): bool
str_starts_with(string $haystack, string $needle): bool
str_ends_with(string $haystack, string $needle): bool
str_pad(string $input, int $length, string $pad = ' ', int $type = STR_PAD_RIGHT): string
str_repeat(string $string, int $times): string
str_replace(array|string $search, array|string $replace, array|string $subject): array|string
str_word_count(string $string, int $format = 0): array|int
strcmp(string $a, string $b): int      // Comparaison sensible à la casse
strcasecmp(string $a, string $b): int  // Insensible à la casse

substr(string $string, int $offset, ?int $length = null): string
substr_count(string $haystack, string $needle): int
substr_replace(array|string $string, array|string $replace, array|int $offset, ...): array|string

sprintf(string $format, mixed ...$values): string
printf(string $format, mixed ...$values): int
number_format(float $num, int $decimals = 0, string $decimal_separator = '.', string $thousands_separator = ','): string

trim(string $string, string $characters = " \t\n\r\0\x0B"): string
ltrim(string $string, string $characters = ...): string
rtrim(string $string, string $characters = ...): string

strtolower(string $string): string
strtoupper(string $string): string
ucfirst(string $string): string
lcfirst(string $string): string
ucwords(string $string, string $separators = " \t\r\n\f\v"): string

strlen(string $string): int
mb_strlen(string $string, ?string $encoding = null): int
mb_strtolower(string $string, ?string $encoding = null): string
mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string

explode(string $separator, string $string, int $limit = PHP_INT_MAX): array
implode(string $separator, array $array): string
join(string $separator, array $array): string   // Alias de implode

nl2br(string $string): string
htmlspecialchars(string $string, int $flags = ENT_QUOTES|ENT_SUBSTITUTE, ?string $encoding = 'UTF-8'): string
htmlspecialchars_decode(string $string, int $flags = ENT_QUOTES|ENT_SUBSTITUTE): string
strip_tags(string $string, array|string|null $allowed_tags = null): string

preg_match(string $pattern, string $subject, array &$matches = [], int $flags = 0, int $offset = 0): int|false
preg_match_all(string $pattern, string $subject, array &$matches = [], int $flags = PREG_PATTERN_ORDER): int|false
preg_replace(array|string $pattern, array|string $replacement, array|string $subject): array|string|null
preg_split(string $pattern, string $subject, int $limit = -1, int $flags = 0): array|false
preg_quote(string $string, ?string $delimiter = null): string
```

### Tableaux

```php
// Création / manipulation
array_keys(array $array, mixed $filter_value = null): array
array_values(array $array): array
array_combine(array $keys, array $values): array
array_fill(int $start_index, int $count, mixed $value): array
array_fill_keys(array $keys, mixed $value): array
range(int|float|string $start, int|float|string $end, int|float $step = 1): array

// Ajout / suppression
array_push(array &$array, mixed ...$values): int
array_pop(array &$array): mixed
array_shift(array &$array): mixed
array_unshift(array &$array, mixed ...$values): int
array_splice(array &$array, int $offset, ?int $length = null, mixed $replacement = []): array

// Recherche
in_array(mixed $needle, array $haystack, bool $strict = false): bool
array_search(mixed $needle, array $haystack, bool $strict = false): int|string|false
array_key_exists(string|int $key, array $array): bool
isset($array[$key]): bool   // Aussi vérifie !== null

// Tri
sort(array &$array, int $flags = SORT_REGULAR): true
rsort(array &$array, int $flags = SORT_REGULAR): true
asort(array &$array, int $flags = SORT_REGULAR): true    // Préserve les clés
arsort(array &$array, int $flags = SORT_REGULAR): true
ksort(array &$array, int $flags = SORT_REGULAR): true    // Tri par clé
krsort(array &$array, int $flags = SORT_REGULAR): true
usort(array &$array, callable $callback): true
uasort(array &$array, callable $callback): true
uksort(array &$array, callable $callback): true

// Transformation
array_map(callable $callback, array $array, array ...$arrays): array
array_filter(array $array, ?callable $callback = null, int $mode = 0): array
array_reduce(array $array, callable $callback, mixed $initial = null): mixed
array_walk(array|object &$array, callable $callback, mixed $arg = null): true

// Découpe / fusion
array_slice(array $array, int $offset, ?int $length = null, bool $preserve_keys = false): array
array_chunk(array $array, int $length, bool $preserve_keys = false): array
array_merge(array ...$arrays): array
array_merge_recursive(array ...$arrays): array
array_replace(array $array, array ...$replacements): array

// Unicité / différence / intersection
array_unique(array $array, int $flags = SORT_STRING): array
array_diff(array $array, array ...$arrays): array
array_diff_key(array $array, array ...$arrays): array
array_intersect(array $array, array ...$arrays): array
array_intersect_key(array $array, array ...$arrays): array

// Flip / reverse
array_flip(array $array): array
array_reverse(array $array, bool $preserve_keys = false): array

// Comptage
count(Countable|array $array, int $mode = COUNT_NORMAL): int
array_count_values(array $array): array

// Fonctions PHP 8.4
array_find(array $array, callable $callback): mixed
array_find_key(array $array, callable $callback): int|string|null
array_any(array $array, callable $callback): bool
array_all(array $array, callable $callback): bool

// Déstructuration
[$a, $b] = [1, 2];
[, $second] = [1, 2];
['name' => $name] = ['name' => 'Alice'];
```

### Mathématiques

```php
abs(int|float $num): int|float
ceil(int|float $num): float
floor(int|float $num): float
round(int|float $num, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float
fmod(float $num1, float $num2): float
intdiv(int $num1, int $num2): int
max(mixed $value, mixed ...$values): mixed
min(mixed $value, mixed ...$values): mixed
pow(int|float $base, int|float $exp): int|float
sqrt(float $num): float
log(float $num, float $base = M_E): float
pi(): float

// Aléatoire (cryptographiquement sûr)
random_int(int $min, int $max): int
random_bytes(int $length): string

// Constantes utiles
PHP_INT_MAX;    // 9223372036854775807
PHP_INT_MIN;    // -9223372036854775808
PHP_FLOAT_MAX;  // 1.7976931348623E+308
PHP_EOL;        // "\n" ou "\r\n" selon l'OS
```

### Dates & Temps

```php
// Objet DateTime / DateTimeImmutable (recommandé)
$dt = new DateTimeImmutable('2024-01-15 10:30:00', new DateTimeZone('Europe/Paris'));
$dt->format('Y-m-d H:i:s');    // "2024-01-15 10:30:00"
$dt->modify('+1 day');          // Retourne un nouvel objet (immutable)
$dt->getTimestamp();            // Unix timestamp

// DateInterval
$interval = new DateInterval('P1Y2M3DT4H5M6S');
$dt->add($interval);
$dt->diff(new DateTimeImmutable('now'));

// DatePeriod
$period = new DatePeriod(
    new DateTimeImmutable('2024-01-01'),
    new DateInterval('P1M'),
    new DateTimeImmutable('2024-12-31')
);
foreach ($period as $date) { /* ... */ }

// Fonctions procédurales (compatibilité)
time(): int                          // Timestamp actuel
mktime(int ...$args): int|false
strtotime(string $datetime, ?int $baseTimestamp = null): int|false
date(string $format, ?int $timestamp = null): string
date_create(string $datetime = 'now'): DateTime|false
```

### JSON

```php
json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
// Flags utiles :
// JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES,
// JSON_THROW_ON_ERROR, JSON_NUMERIC_CHECK, JSON_FORCE_OBJECT

json_decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0): mixed
// $associative = true → array, false → stdClass, null → auto

// Bonne pratique : toujours utiliser JSON_THROW_ON_ERROR
try {
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    $encoded = json_encode($data, JSON_THROW_ON_ERROR);
} catch (\JsonException $e) {
    // Erreur de parsing
}
```

### Fichiers & Système

```php
file_get_contents(string $filename, bool $use_include_path = false, ?resource $context = null): string|false
file_put_contents(string $filename, mixed $data, int $flags = 0, ?resource $context = null): int|false
// Flags : FILE_APPEND, LOCK_EX

file_exists(string $filename): bool
is_file(string $filename): bool
is_dir(string $filename): bool
is_readable(string $filename): bool
is_writable(string $filename): bool

mkdir(string $directory, int $permissions = 0777, bool $recursive = false): bool
rmdir(string $directory): bool
unlink(string $filename): bool
rename(string $from, string $to): bool
copy(string $from, string $to): bool

realpath(string $path): string|false
dirname(string $path, int $levels = 1): string
basename(string $path, string $suffix = ''): string
pathinfo(string $path, int $options = PATHINFO_ALL): array|string

scandir(string $directory, int $sorting_order = SCANDIR_SORT_ASCENDING): array|false
glob(string $pattern, int $flags = 0): array|false

// SPL (recommandé pour parcourir les dossiers)
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/path'));
foreach ($it as $file) { /* SplFileInfo */ }
```

---

## POO — Classes et Interfaces

### Structure d'une classe

```php
class Animal {
    // Constantes
    public const MAX_AGE = 30;
    protected const TYPE = 'animal';

    // Propriétés statiques
    private static int $count = 0;

    // Constructeur avec promotion de propriétés (8.0+)
    public function __construct(
        public readonly string $name,
        protected int $age = 0,
        private ?string $species = null,
    ) {
        self::$count++;
    }

    // Méthodes magiques communes
    public function __toString(): string { return $this->name; }
    public function __clone() { /* deep copy si nécessaire */ }
    public function __destruct() { self::$count--; }

    // Méthode statique
    public static function getCount(): int { return self::$count; }

    // Late static binding
    public static function create(): static { return new static(...func_get_args()); }
}
```

### Interfaces & Traits

```php
interface Serializable {
    public function serialize(): string;
    public function unserialize(string $data): void;
}

interface Loggable extends Serializable {
    public function log(): void;
}

trait HasTimestamps {
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    public function touch(): void {
        $this->updatedAt = new DateTimeImmutable();
    }

    abstract protected function save(): void; // Méthode abstraite dans un trait
}

class Entity implements Loggable {
    use HasTimestamps;

    public function serialize(): string { return json_encode(get_object_vars($this)); }
    public function unserialize(string $data): void { /* ... */ }
    public function log(): void { /* ... */ }
    protected function save(): void { /* ... */ }
}
```

### Classes abstraites et finales

```php
abstract class Shape {
    abstract public function area(): float;
    abstract public function perimeter(): float;

    public function describe(): string {
        return sprintf("Area: %.2f, Perimeter: %.2f", $this->area(), $this->perimeter());
    }
}

final class Circle extends Shape {
    public function __construct(private readonly float $radius) {}
    public function area(): float { return M_PI * $this->radius ** 2; }
    public function perimeter(): float { return 2 * M_PI * $this->radius; }
}
```

### Magic Methods

```php
__construct()         // Constructeur
__destruct()          // Destructeur
__toString(): string  // Conversion en chaîne
__invoke(mixed ...$args) // Appelé comme fonction : $obj(args)
__clone()             // Appelé par clone $obj
__get(string $name): mixed         // Accès propriété inaccessible
__set(string $name, mixed $value)  // Écriture propriété inaccessible
__isset(string $name): bool        // isset() sur propriété inaccessible
__unset(string $name)              // unset() sur propriété inaccessible
__call(string $name, array $args): mixed          // Méthode inaccessible
__callStatic(string $name, array $args): mixed    // Méthode statique inaccessible
__serialize(): array                              // Remplace __sleep
__unserialize(array $data): void                 // Remplace __wakeup
__debugInfo(): array                             // var_dump() personnalisé
```

---

## Exceptions & Gestion d'Erreurs

```php
// Hiérarchie
Throwable
├── Error
│   ├── TypeError
│   ├── ValueError
│   ├── ArithmeticError (DivisionByZeroError)
│   ├── ParseError
│   └── UnhandledMatchError (8.0+)
└── Exception
    ├── RuntimeException
    │   ├── OutOfBoundsException
    │   ├── OverflowException
    │   └── UnexpectedValueException
    ├── LogicException
    │   ├── BadMethodCallException
    │   ├── InvalidArgumentException
    │   └── OutOfRangeException
    └── JsonException

// Bonne pratique
try {
    // Code risqué
} catch (TypeError | ValueError $e) {
    // Gestion de plusieurs types (8.0+ : union dans catch)
} catch (RuntimeException $e) {
    // Erreur d'exécution
} finally {
    // Toujours exécuté
}

// Custom exception
class DomainException extends \RuntimeException {
    public function __construct(
        string $message,
        private readonly array $context = [],
        \Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getContext(): array { return $this->context; }
}

// set_exception_handler
set_exception_handler(function (\Throwable $e): void {
    error_log($e->getMessage());
});

// set_error_handler (convertit les erreurs PHP en exceptions)
set_error_handler(function (int $errno, string $errstr, string $file, int $line): bool {
    throw new \ErrorException($errstr, 0, $errno, $file, $line);
});
```

---

## Générateurs (Generators)

```php
function fibonacci(): Generator {
    [$a, $b] = [0, 1];
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

$gen = fibonacci();
$gen->current();  // 0
$gen->next();
$gen->current();  // 1

// yield avec clé
function indexedLines(string $file): Generator {
    $fh = fopen($file, 'r');
    $line = 0;
    while (($row = fgets($fh)) !== false) {
        yield $line++ => trim($row);
    }
    fclose($fh);
}

// yield from (délégation)
function combined(): Generator {
    yield from [1, 2, 3];
    yield from otherGenerator();
}

// send() pour envoyer une valeur dans le générateur
function accumulator(): Generator {
    $total = 0;
    while (true) {
        $value = yield $total;
        if ($value === null) break;
        $total += $value;
    }
}
$acc = accumulator();
$acc->current(); // 0 — démarre le générateur
$acc->send(10);  // 10
$acc->send(5);   // 15
```

---

## Closures & Fonctions

```php
// Closure
$multiply = function(int $a, int $b): int { return $a * $b; };

// use — capture de variables
$factor = 3;
$triple = function(int $x) use ($factor): int { return $x * $factor; };
$tripleRef = function(int $x) use (&$factor): int { return $x * $factor; }; // Par référence

// Arrow function (8.0+) — capture automatique par valeur
$triple = fn(int $x): int => $x * $factor;

// Closure::bind / bindTo
class Counter { private int $count = 0; }
$increment = Closure::bind(
    fn() => ++$this->count,
    new Counter(),
    Counter::class
);

// Callable types
$callables = [
    'strlen',                    // Nom de fonction
    [$obj, 'method'],            // Méthode d'instance
    [MyClass::class, 'static'],  // Méthode statique
    MyClass::class . '::static', // Méthode statique (syntaxe string)
    $closure,                    // Closure
    strlen(...),                 // First-class callable (8.1+)
];
```

---

## SPL (Standard PHP Library)

### Structures de données

```php
SplStack          // LIFO
SplQueue          // FIFO
SplPriorityQueue  // File de priorité
SplDoublyLinkedList
SplFixedArray     // Array de taille fixe (plus performant)
SplMinHeap / SplMaxHeap

// Exemple SplStack
$stack = new SplStack();
$stack->push('a');
$stack->push('b');
$stack->top();  // 'b'
$stack->pop();  // 'b'
```

### Itérateurs

```php
ArrayIterator, ArrayObject
RecursiveArrayIterator
DirectoryIterator, FilesystemIterator
RecursiveDirectoryIterator + RecursiveIteratorIterator
GlobIterator
FilterIterator (classe abstraite à étendre)
LimitIterator, CallbackFilterIterator (8.0+)

// CallbackFilterIterator (8.0+)
$filtered = new CallbackFilterIterator(
    new ArrayIterator([1, 2, 3, 4, 5]),
    fn($v) => $v % 2 === 0
);
```

---

## Interfaces Natives Importantes

```php
Countable       { count(): int }
Iterator        { current, key, next, rewind, valid }
IteratorAggregate { getIterator(): Traversable }
Stringable      { __toString(): string }
Throwable
ArrayAccess     { offsetExists, offsetGet, offsetSet, offsetUnset }
Serializable    { serialize, unserialize }
JsonSerializable { jsonSerialize(): mixed }
Comparable (via spaceship <=>)
```

---

## Gestion de la Mémoire & Performance

```php
// Désactiver les références circulaires
unset($obj);
gc_collect_cycles();
gc_enabled(); gc_enable(); gc_disable();

// Limites
memory_get_usage(bool $real_usage = false): int
memory_get_peak_usage(bool $real_usage = false): int
ini_get('memory_limit');

// Profilage simple
$start = hrtime(true);   // Nanosecondes
// ... code ...
$elapsed = (hrtime(true) - $start) / 1e9; // En secondes

// Opcache
opcache_get_status();
opcache_invalidate(string $filename);
opcache_reset();
```

---

## Sécurité

```php
// Hachage de mots de passe
password_hash(string $password, PASSWORD_BCRYPT | PASSWORD_ARGON2ID | PASSWORD_DEFAULT): string
password_verify(string $password, string $hash): bool
password_needs_rehash(string $hash, int $algo): bool
password_info(string $hash): array

// Données binaires sécurisées
random_bytes(int $length): string
random_int(int $min, int $max): int
bin2hex(string $string): string
hex2bin(string $string): string|false

// Comparaison sécurisée (contre timing attacks)
hash_equals(string $known_string, string $user_string): bool

// Filtres
filter_var(mixed $value, int $filter = FILTER_DEFAULT, array|int $options = 0): mixed
filter_var($email, FILTER_VALIDATE_EMAIL);
filter_var($url, FILTER_VALIDATE_URL);
filter_var($int, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);

// Préparation SQL (PDO)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
```

---

## PDO (Accès Base de Données)

```php
// Connexion
$pdo = new PDO(
    dsn: 'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
    username: 'root',
    password: 'secret',
    options: [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

// Requêtes préparées
$stmt = $pdo->prepare("SELECT * FROM users WHERE active = :active AND age > :age");
$stmt->execute([':active' => true, ':age' => 18]);
$users = $stmt->fetchAll();                  // array
$user  = $stmt->fetch();                     // une ligne
$name  = $stmt->fetchColumn(0);             // première colonne

// Fetch modes
PDO::FETCH_ASSOC    // Tableau associatif
PDO::FETCH_OBJ      // stdClass
PDO::FETCH_CLASS    // Classe personnalisée
PDO::FETCH_NUM      // Tableau indexé
PDO::FETCH_COLUMN   // Colonne unique

$stmt->setFetchMode(PDO::FETCH_CLASS, User::class);

// Transactions
$pdo->beginTransaction();
try {
    $pdo->exec("INSERT ...");
    $pdo->exec("UPDATE ...");
    $pdo->commit();
} catch (\Exception $e) {
    $pdo->rollBack();
    throw $e;
}

// Insert et récupération de l'ID
$stmt = $pdo->prepare("INSERT INTO posts (title) VALUES (:title)");
$stmt->execute([':title' => 'Hello']);
$id = $pdo->lastInsertId();
```

---

## Constantes PHP Utiles

```php
PHP_VERSION         // "8.4.x"
PHP_MAJOR_VERSION   // 8
PHP_MINOR_VERSION   // 4
PHP_EOL             // "\n" ou "\r\n"
PHP_INT_MAX         // 9223372036854775807
PHP_INT_MIN         // -9223372036854775808
PHP_FLOAT_EPSILON   // 2.2204460492503E-16
PHP_INT_SIZE        // 8 (bytes)
DIRECTORY_SEPARATOR // "/" ou "\"
PATH_SEPARATOR      // ":" ou ";"
PHP_MAXPATHLEN      // Longueur max d'un chemin
E_ALL               // Tous les niveaux d'erreur
E_ERROR, E_WARNING, E_NOTICE, E_DEPRECATED

TRUE, FALSE, NULL
INF, NAN

// Constantes magiques
__LINE__      // Ligne courante
__FILE__      // Chemin du fichier courant
__DIR__       // Dossier du fichier courant
__FUNCTION__  // Nom de la fonction courante
__CLASS__     // Nom de la classe courante
__METHOD__    // Nom de la méthode courante (Classe::méthode)
__TRAIT__     // Nom du trait courant
__NAMESPACE__ // Namespace courant
```

---

## Bonnes Pratiques PHP 8.4

1. **Utiliser les types stricts** : `declare(strict_types=1);` en haut de chaque fichier.
2. **Préférer `DateTimeImmutable`** à `DateTime` pour éviter les mutations.
3. **Utiliser `match` plutôt que `switch`** pour bénéficier de la comparaison stricte.
4. **Nullsafe operator `?->`** pour les chaînes nullables.
5. **Named arguments** pour les fonctions avec beaucoup de paramètres optionnels.
6. **`readonly` properties** pour les objets value-object.
7. **Enums** à la place des constantes de classe pour les états finis.
8. **`json_encode` / `json_decode` avec `JSON_THROW_ON_ERROR`**.
9. **PDO avec `PDO::ERRMODE_EXCEPTION`** et requêtes préparées exclusivement.
10. **`password_hash` + `password_verify`** pour les mots de passe.
11. **`random_int` / `random_bytes`** pour toute génération aléatoire sécurisée.
12. **Property hooks (8.4)** pour remplacer les getters/setters verbeux.
13. **Asymmetric visibility (8.4)** pour un meilleur encapsulation sans boilerplate.

---

*Référence générée pour PHP 8.4 — compatible avec les fonctionnalités de PHP 8.0 à 8.4.*
