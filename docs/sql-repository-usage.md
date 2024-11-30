# Using the Sql Base Repository

The `Sql` abstract class serves as a base for creating repository classes that
interact with the database. By extending `Sql`, you can perform common CRUD 
operations using reusable methods and avoid writing SQL code directly in your repository.

## Purpose

The `Sql` class offers:
- **Transaction Management** via traits like `TransactionTrait`
- **Reusable CRUD operations** such as `insert`, `update`, `delete`, and `deleteBy`
- **Flexible Query Building** based on doctrine dbal` classes

The goal is to streamline the creation of module-specific repositories by extending `Sql` and defining only the additional methods required for each module.

## Example: Creating a UserMain Repository

Let's create a `UserMain` repository by extending `Sql`. This repository will be responsible for interacting with the `user_main` table.

### Step 1: Define the UserMain Repository

In your `UserMain` repository, extend the `Sql` class and define the table and primary key (`id_field`):

```php
namespace App\Modules\Auth\Repositories;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\Entities\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Provides user data handling for authentication.
 */
class UserMain extends Sql
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'user_main', 'UID');
	}

	/**
	 * @param string $identifier
	 *
	 * @return User
	 * @throws UserException
	 * @throws Exception
	 */
	public function loadUserByIdentifier(string $identifier): User
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('*')->from($this->table);

		if (filter_var($identifier, FILTER_VALIDATE_EMAIL))
			$queryBuilder->where('email = :identifier');
		else
			$queryBuilder->where('username = :identifier');
		$queryBuilder->setParameter('identifier', $identifier);

		$result = $queryBuilder->executeQuery()->fetchAssociative();
		if (!$result || empty($result))
			throw new UserException('User not found.');

		return new User($result);
	}
}
```

## Step 2: Using UserMain Repository Methods
The `UserMain` repository inherits all CRUD operations from `Sql`, which we can use directly without additional setup.

### Inserting a User

```php
$UserMainRepo = new UserMain($dbh, $queryBuilder, $dataPreparer);

$user_data = [
    'username' => 'johndoe',
    'password' => password_hash('securepass', PASSWORD_BCRYPT),
    'email'    => 'johndoe@example.com'
];

$user_is = $UserMainRepo->insert($user_data);
echo "Inserted user ID: $user_id";
```

### Updating a User
To update a user’s information by ID:

```php
$updateData = ['email' => 'john.doe@example.com'];
$rowsAffected = $userMainRepo->update(123, $updateData);
echo "Updated $rowsAffected rows.";
```
### Deleting a User by ID
To delete a user by their unique identifier:
    
```php
$rowsDeleted = $userMainRepo->delete(123);
echo "Deleted $rowsDeleted rows.";
```
# Using Custom Queries in the Repository
In addition to the inherited methods, you can add custom query methods to the repository. Here’s an example that finds a user by username:   

```php
 $user = $userMainRepo->findByUsername('johndoe');
if ($user)
 {
    echo "Found user: " . $user->getUsername();
}
 else
  {
    echo "User not found.";
}
```

### Additional Inherited Methods
The `Sql` base class provides additional methods to support complex queries and flexible deletion options:

- `deleteByField(string $field, mixed $value, string $limit = '')`: Deletes records based on a field-value pair.
- `deleteBy(string $where, string $limit = '')`: Deletes records based on a custom WHERE clause.
- `updateWithWhere(array $fields, string $where)`: Updates records with a custom WHERE clause.

For example, to delete users by email:

```php
$rowsDeleted = $userMainRepo->deleteByField('email', 'johndoe@example.com');

```
### Summary
The Sql class provides a foundation for building repositories with minimal code. By extending it, repositories like `UserMain` gain flexible, reusable CRUD operations and the ability to add custom queries tailored to specific modules. This approach reduces duplication and promotes a clean, modular structure in your application.