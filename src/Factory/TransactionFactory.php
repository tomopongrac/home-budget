<?php

namespace App\Factory;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Transaction>
 *
 * @method        Transaction|Proxy                     create(array|callable $attributes = [])
 * @method static Transaction|Proxy                     createOne(array $attributes = [])
 * @method static Transaction|Proxy                     find(object|array|mixed $criteria)
 * @method static Transaction|Proxy                     findOrCreate(array $attributes)
 * @method static Transaction|Proxy                     first(string $sortedField = 'id')
 * @method static Transaction|Proxy                     last(string $sortedField = 'id')
 * @method static Transaction|Proxy                     random(array $attributes = [])
 * @method static Transaction|Proxy                     randomOrCreate(array $attributes = [])
 * @method static TransactionRepository|RepositoryProxy repository()
 * @method static Transaction[]|Proxy[]                 all()
 * @method static Transaction[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Transaction[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Transaction[]|Proxy[]                 findBy(array $attributes)
 * @method static Transaction[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Transaction[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Transaction> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Transaction> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Transaction> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Transaction> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Transaction> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Transaction> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Transaction> random(array $attributes = [])
 * @phpstan-method static Proxy<Transaction> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Transaction> repository()
 * @phpstan-method static list<Proxy<Transaction>> all()
 * @phpstan-method static list<Proxy<Transaction>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Proxy<Transaction>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Proxy<Transaction>> findBy(array $attributes)
 * @phpstan-method static list<Proxy<Transaction>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Proxy<Transaction>> randomSet(int $number, array $attributes = [])
 */
final class TransactionFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'activeAt' => self::faker()->dateTime(),
            'amountCents' => self::faker()->randomNumber(5),
            'category' => CategoryFactory::new(),
            'title' => self::faker()->text(255),
            'type' => TransactionType::cases()[array_rand(TransactionType::cases())],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Transaction $transaction): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Transaction::class;
    }
}
