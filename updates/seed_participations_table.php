<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Participation;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Person;
use October\Rain\Database\Updates\Seeder;
use Str;

class SeedRolesTable extends Seeder
{
	public function run()
	{
		require_once 'participations.php';

		foreach ($roles as $performance)
		{

			$id = $this->findPerformance($performance['title']);

			$this->createRoles($performance, $id, 'creators');
			$this->createRoles($performance, $id, 'roles');
		}
	}

	private function createRoles($performance, $id, $type)
	{
		foreach ($performance[$type] as $key => $item)
		{

			$person_id = $this->findPerson($item['name']);

			$performance_id = $id;


			if ( array_key_exists('name', $item) )
				$name = $item['name'];
			else
				$name = NULL;


			if ( array_key_exists('role', $item) )
				$role = $item['role'];
			else
				$role = "-";


			if ( array_key_exists('group', $item) )
				$group = $item['group'];
			else
				$group = NULL;


			if ( array_key_exists('description', $item) )
				$description = $item['description'];
			else
				$description = NULL;


			// echo mb_convert_encoding($role, "CP866") . "\n";
			// echo mb_convert_encoding($name, "CP866") . "\n";
			// echo mb_convert_encoding($group, "CP866") . "\n";

			$role = [
				'title' => $role,
				'performance_id' => $performance_id,
				'person_id' => $person_id,
				'description' => $description,
				'group' => $group,
				'type' => $type,
				'performance_title' => $performance['title'],
				'person_name' => $name,
			];

			Participation::create($role);
		}
	}

	private function findPerson($name)
	{

		$post = Person::where('title', '=', $name)->first();

		if (is_null($post))
		{
			// echo "NOT FIND! Create...\n";
			return $this->createPerson($name);
		}
		else
		{
			// echo "FIND! Id: " . $post->id . "\n";
			return $post->id;
		}
	}

	private function createPerson($name)
	{
		// echo "Create person: " . mb_convert_encoding($name, "CP866") . "\n";
		$person = [
			'title' => $name,
			'family_name' => explode(' ', $name)[1],
			'given_name' => explode(' ', $name)[0],
			'slug' => Str::slug($name),
		];
		// print_r($person);
		$ret = Person::create($person);
		return $ret->id;
	}


	private function findPerformance($name)
	{
		$post = Performance::where('title', '=', $name)->first();

		// echo "\n\n\n  " . mb_convert_encoding($name, "CP866") . ": " . $post->id . "\n\n";

		return $post->id;
	}

}