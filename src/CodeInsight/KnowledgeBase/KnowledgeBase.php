<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\KnowledgeBase;


use Aura\Sql\ExtendedPdoInterface;
use Composer\Autoload\ClassLoader;
use ConsoleHelpers\ConsoleKit\ConsoleIO;
use Go\ParserReflection\Locator\CallableLocator;
use Go\ParserReflection\Locator\ComposerLocator;
use Go\ParserReflection\LocatorInterface;
use Go\ParserReflection\ReflectionEngine;
use Go\ParserReflection\ReflectionFile;
use Symfony\Component\Finder\Finder;

class KnowledgeBase
{

	const SCOPE_PRIVATE = 1;

	const SCOPE_PROTECTED = 2;

	const SCOPE_PUBLIC = 3;

	const CLASS_TYPE_CLASS = 1;

	const CLASS_TYPE_INTERFACE = 2;

	const CLASS_TYPE_TRAIT = 3;

	const RELATION_TYPE_EXTENDS = 1;

	const RELATION_TYPE_IMPLEMENTS = 2;

	/**
	 * Project path.
	 *
	 * @var string
	 */
	protected $projectPath = '';

	/**
	 * Regular expression for removing project path.
	 *
	 * @var string
	 */
	protected $projectPathRegExp = '';

	/**
	 * Database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $db;

	/**
	 * Config
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Console IO.
	 *
	 * @var ConsoleIO
	 */
	protected $io;

	/**
	 * Creates knowledge base instance.
	 *
	 * @param string               $project_path Project path.
	 * @param ExtendedPdoInterface $db           Database.
	 * @param ConsoleIO            $io           Console IO.
	 *
	 * @throws \InvalidArgumentException When project path doesn't exist.
	 */
	public function __construct($project_path, ExtendedPdoInterface $db, ConsoleIO $io = null)
	{
		if ( !file_exists($project_path) || !is_dir($project_path) ) {
			throw new \InvalidArgumentException('The project path doesn\'t exist.');
		}

		$this->projectPath = $project_path;
		$this->projectPathRegExp = '#^' . preg_quote($project_path, '#') . '/#';

		$this->db = $db;
		$this->config = $this->getConfiguration();
		$this->io = $io;
	}

	/**
	 * Returns project configuration.
	 *
	 * @return array
	 * @throws \LogicException When configuration file is not found.
	 * @throws \LogicException When configuration file isn't in JSON format.
	 */
	protected function getConfiguration()
	{
		$config_file = $this->projectPath . '/.code-insight.json';

		if ( !file_exists($config_file) ) {
			throw new \LogicException(
				'Configuration file ".code-insight.json" not found at "' . $this->projectPath . '".'
			);
		}

		$config = json_decode(file_get_contents($config_file), true);

		if ( $config === null ) {
			throw new \LogicException('Configuration file ".code-insight.json" is not in JSON format.');
		}

		return $config;
	}

	/**
	 * Refreshes database.
	 *
	 * @return void
	 * @throws \LogicException When "$this->io" wasn't set upfront.
	 */
	public function refresh()
	{
		if ( !isset($this->io) ) {
			throw new \LogicException('The "$this->io" must be set prior to calling "$this->refresh()".');
		}

		//ReflectionEngine::$maximumCachedFiles = 10;
		ReflectionEngine::init($this->detectClassLocator());

		$sql = 'UPDATE Files
				SET Found = 0';
		$this->db->perform($sql);

		$files = array();
		$this->io->write('Searching for files ... ');

		foreach ( $this->getFinders() as $finder ) {
			$files = array_merge($files, array_keys(iterator_to_array($finder)));
		}

		$file_count = count($files);
		$this->io->writeln(array('<info>' . $file_count . ' found</info>', ''));


		$progress_bar = $this->io->createProgressBar($file_count + 2);
		$progress_bar->setMessage('');
		$progress_bar->setFormat(
			'%message%' . PHP_EOL . '%current%/%max% [%bar%] <info>%percent:3s%%</info> %elapsed:6s%/%estimated:-6s% <info>%memory:-10s%</info>'
		);
		$progress_bar->start();

		foreach ( $files as $file ) {
			$progress_bar->setMessage('Processing File: <info>' . $this->removeProjectPath($file) . '</info>');
			$progress_bar->display();

			$this->processFile($file);

			$progress_bar->advance();
		}

		$sql = 'SELECT Id
				FROM Files
				WHERE Found = 0';
		$deleted_files = $this->db->fetchCol($sql);

		if ( $deleted_files ) {
			$progress_bar->setMessage('Deleting Files ...');
			$progress_bar->display();

			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId IN (:file_ids)';
			$deleted_classes = $this->db->fetchCol($sql, array(
				'file_ids' => $deleted_files,
			));

			foreach ( $deleted_classes as $deleted_class_id ) {
				$this->deleteClass($deleted_class_id);
			}

			$progress_bar->advance();
		}

		$progress_bar->setMessage('Processing Class Relations ...');
		$progress_bar->display();

		$this->processClassRawRelations();

		$progress_bar->advance();

		$progress_bar->finish();
		$progress_bar->clear();


	}

	/**
	 * Prints statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		$ret = array();

		$sql = 'SELECT COUNT(*)
				FROM Files';
		$ret['Files'] = $this->db->fetchValue($sql);

		$sql = 'SELECT FileId
				FROM Classes
				GROUP BY FileId
				HAVING COUNT(*) > 1';
		$ret['Files With Multiple Classes'] = count($this->db->fetchCol($sql));

		$sql = 'SELECT ClassType, COUNT(*)
				FROM Classes
				GROUP BY ClassType';
		$classes_count = $this->db->fetchPairs($sql);

		foreach ( $classes_count as $class_type => $class_count ) {
			$title = 'Unknowns';

			if ( $class_type === self::CLASS_TYPE_CLASS ) {
				$title = 'Classes';
			}
			elseif ( $class_type === self::CLASS_TYPE_INTERFACE ) {
				$title = 'Interfaces';
			}
			elseif ( $class_type === self::CLASS_TYPE_TRAIT ) {
				$title = 'Traits';
			}

			$ret[$title] = $class_count;
		}

		return $ret;
	}

	/**
	 * Processes file.
	 *
	 * @param string $file File.
	 *
	 * @return integer
	 */
	protected function processFile($file)
	{
		$size = filesize($file);
		$relative_file = $this->removeProjectPath($file);

		$sql = 'SELECT Id, Size
				FROM Files
				WHERE Name = :name';
		$file_data = $this->db->fetchOne($sql, array(
			'name' => $relative_file,
		));

		$this->db->beginTransaction();

		if ( $file_data === false ) {
			$sql = 'INSERT INTO Files (Name, Size) VALUES (:name, :size)';
			$this->db->perform($sql, array(
				'name' => $relative_file,
				'size' => $size,
			));

			$file_id = $this->db->lastInsertId();
		}
		else {
			$file_id = $file_data['Id'];
		}

		// File is not changed since last time it was indexed.
		if ( $file_data !== false && (int)$file_data['Size'] === $size ) {
			$sql = 'UPDATE Files
					SET Found = 1
					WHERE Id = :file_id';
			$this->db->perform($sql, array(
				'file_id' => $file_data['Id'],
			));

			$this->db->commit();

			return $file_data['Id'];
		}

		$sql = 'UPDATE Files
				SET Found = 1
				WHERE Id = :file_id';
		$this->db->perform($sql, array(
			'file_id' => $file_data['Id'],
		));

		$new_classes = array();
		$parsed_file = new ReflectionFile($file);

		foreach ( $parsed_file->getFileNamespaces() as $namespace ) {
			foreach ( $namespace->getClasses() as $class ) {
				$new_classes[] = $class->getName();
				$this->processClass($file_id, $class);
			}
		}

		if ( $new_classes ) {
			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId = :file_id AND Name NOT IN (:classes)';
			$deleted_classes = $this->db->fetchCol($sql, array(
				'file_id' => $file_id,
				'classes' => $new_classes,
			));
		}
		else {
			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId = :file_id';
			$deleted_classes = $this->db->fetchCol($sql, array(
				'file_id' => $file_id,
			));
		}

		foreach ( $deleted_classes as $deleted_class_id ) {
			$this->deleteClass($deleted_class_id);
		}

		$this->db->commit();

		ReflectionEngine::unsetFile($file);

		return $file_id;
	}

	/**
	 * Processes class.
	 *
	 * @param integer          $file_id File ID.
	 * @param \ReflectionClass $class   Class.
	 *
	 * @return void
	 */
	protected function processClass($file_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Id
				FROM Classes
				WHERE FileId = :file_id AND Name = :name';
		$class_id = $this->db->fetchValue($sql, array(
			'file_id' => $file_id,
			'name' => $class->getName(),
		));

		$raw_class_relations = $this->getRawClassRelations($class);

		if ( $class_id === false ) {
			$sql = 'INSERT INTO Classes (Name, ClassType, IsAbstract, IsFinal, FileId, RawRelations)
					VALUES (:name, :class_type, :is_abstract, :is_final, :file_id, :raw_relations)';

			$this->db->perform(
				$sql,
				array(
					'name' => $class->getName(),
					'class_type' => $this->getClassType($class),
					'is_abstract' => (int)$class->isAbstract(),
					'is_final' => (int)$class->isFinal(),
					'file_id' => $file_id,
					'raw_relations' => $raw_class_relations ? json_encode($raw_class_relations) : null,
				)
			);

			$class_id = $this->db->lastInsertId();
		}
		else {
			$sql = 'UPDATE Classes
					SET ClassType = :class_type, IsAbstract = :is_abstract, IsFinal = :is_final, RawRelations = :raw_relations
					WHERE Id = :class_id';

			$this->db->perform(
				$sql,
				array(
					'class_type' => $this->getClassType($class),
					'is_abstract' => (int)$class->isAbstract(),
					'is_final' => (int)$class->isFinal(),
					'raw_relations' => $raw_class_relations ? json_encode($raw_class_relations) : null,
					'class_id' => $class_id,
				)
			);
		}

		$this->processClassConstants($class_id, $class);
		$this->processClassProperties($class_id, $class);
		$this->processClassMethods($class_id, $class);
	}

	/**
	 * Returns class type.
	 *
	 * @param \ReflectionClass $class Class.
	 *
	 * @return integer
	 */
	protected function getClassType(\ReflectionClass $class)
	{
		if ( $class->isInterface() ) {
			return self::CLASS_TYPE_INTERFACE;
		}

		if ( $class->isTrait() ) {
			return self::CLASS_TYPE_TRAIT;
		}

		return self::CLASS_TYPE_CLASS;
	}

	/**
	 * Get relations.
	 *
	 * @param \ReflectionClass $class Class.
	 *
	 * @return array
	 */
	protected function getRawClassRelations(\ReflectionClass $class)
	{
		$raw_relations = array();
		$parent_class = $class->getParentClass();

		if ( $parent_class ) {
			$raw_relations[] = array(
				$parent_class->getName(),
				self::RELATION_TYPE_EXTENDS,
				$parent_class->isInternal(),
			);
		}

		foreach ( $class->getInterfaces() as $interface ) {
			$raw_relations[] = array(
				$interface->getName(),
				self::RELATION_TYPE_IMPLEMENTS,
				$interface->isInternal(),
			);
		}

		return $raw_relations;
	}

	/**
	 * Deletes a class.
	 *
	 * @param integer $class_id Class ID.
	 *
	 * @return void
	 */
	protected function deleteClass($class_id)
	{
		$sql = 'DELETE FROM ClassConstants WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$sql = 'DELETE FROM ClassProperties WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$this->deleteClassMethods($class_id, array());

		$sql = 'DELETE FROM ClassRelations WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$sql = 'DELETE FROM ClassRelations WHERE RelatedClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));
	}

	/**
	 * Processes constants.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassConstants($class_id, \ReflectionClass $class)
	{
		$constants = $class->getConstants();

		$sql = 'SELECT Name
				FROM ClassConstants
				WHERE ClassId = :class_id';
		$old_constants = $this->db->fetchCol($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = 'INSERT INTO ClassConstants (ClassId, Name, Value) VALUES (:class_id, :name, :value)';
		$update_sql = 'UPDATE ClassConstants SET Value = :value WHERE ClassId = :class_id AND Name = :name';

		foreach ( $constants as $constant_name => $constant_value ) {
			$this->db->perform(
				in_array($constant_name, $old_constants) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $constant_name,
					'value' => json_encode($constant_value),
				)
			);
		}

		$deleted_constants = array_diff($old_constants, array_keys($constants));

		if ( $deleted_constants ) {
			$sql = 'DELETE FROM ClassConstants
					WHERE ClassId = :class_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'class_id' => $class_id,
				'names' => $deleted_constants,
			));
		}
	}

	/**
	 * Processes properties.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassProperties($class_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Name
				FROM ClassProperties
				WHERE ClassId = :class_id';
		$old_properties = $this->db->fetchCol($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = '	INSERT INTO ClassProperties (ClassId, Name, Value, Scope, IsStatic)
						VALUES (:class_id, :name, :value, :scope, :is_static)';
		$update_sql = '	UPDATE ClassProperties
						SET Value = :value, Scope = :scope, IsStatic = :is_static
						WHERE ClassId = :class_id AND Name = :name';

		$new_properties = array();
		$property_defaults = $class->getDefaultProperties();
		$static_properties = $class->getStaticProperties();
		$class_name = $class->getName();

		foreach ( $class->getProperties() as $property ) {
			if ( $property->class !== $class_name ) {
				continue;
			}

			$property_name = $property->getName();
			$property_value = isset($property_defaults[$property_name]) ? $property_defaults[$property_name] : null;
			$new_properties[] = $property_name;

			$this->db->perform(
				in_array($property_name, $old_properties) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $property_name,
					'value' => json_encode($property_value),
					'scope' => $this->getPropertyScope($property),
					'is_static' => (int)in_array($property_name, $static_properties),
				)
			);
		}

		$deleted_properties = array_diff($old_properties, $new_properties);

		if ( $deleted_properties ) {
			$sql = 'DELETE FROM ClassProperties
					WHERE ClassId = :class_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'class_id' => $class_id,
				'names' => $deleted_properties,
			));
		}
	}

	/**
	 * Returns property scope.
	 *
	 * @param \ReflectionProperty $property Property.
	 *
	 * @return integer
	 */
	protected function getPropertyScope(\ReflectionProperty $property)
	{
		if ( $property->isPrivate() ) {
			return self::SCOPE_PRIVATE;
		}

		if ( $property->isProtected() ) {
			return self::SCOPE_PROTECTED;
		}

		return self::SCOPE_PUBLIC;
	}

	/**
	 * Processes methods.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassMethods($class_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Name, Id
				FROM ClassMethods
				WHERE ClassId = :class_id';
		$old_methods = $this->db->fetchPairs($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = '	INSERT INTO ClassMethods (ClassId, Name, ParameterCount, RequiredParameterCount, Scope, IsAbstract, IsFinal, IsStatic, ReturnsReference, HasReturnType, ReturnType)
						VALUES (:class_id, :name, :parameter_count, :required_parameter_count, :scope, :is_abstract, :is_final, :is_static, :returns_reference, :has_return_type, :return_type)';
		$update_sql = '	UPDATE ClassMethods
						SET ParameterCount = :parameter_count, RequiredParameterCount = :required_parameter_count, Scope = :scope, IsAbstract = :is_abstract, IsFinal = :is_final, IsStatic = :is_static, ReturnsReference = :returns_reference, ReturnType = :return_type, HasReturnType = :has_return_type
						WHERE ClassId = :class_id AND Name = :name';

		$new_methods = array();
		$class_name = $class->getName();

		foreach ( $class->getMethods() as $method ) {
			if ( $method->class !== $class_name ) {
				continue;
			}

			$method_name = $method->getName();
			$new_methods[] = $method_name;

			// Doesn't work for parent classes (see https://github.com/goaop/parser-reflection/issues/16).
			$has_return_type = $method->hasReturnType();
			$return_type = $has_return_type ? (string)$method->getReturnType() : null;

			$this->db->perform(
				isset($old_methods[$method_name]) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $method_name,
					'parameter_count' => $method->getNumberOfParameters(),
					'required_parameter_count' => $method->getNumberOfRequiredParameters(),
					'scope' => $this->getMethodScope($method),
					'is_abstract' => (int)$method->isAbstract(),
					'is_final' => (int)$method->isFinal(),
					'is_static' => (int)$method->isStatic(),
					'returns_reference' => (int)$method->returnsReference(),
					'has_return_type' => (int)$has_return_type,
					'return_type' => $return_type,
				)
			);

			$method_id = isset($old_methods[$method_name]) ? $old_methods[$method_name] : $this->db->lastInsertId();
			$this->processClassMethodParameters($method_id, $method);
		}

		$deleted_methods = array_diff($old_methods, $new_methods);

		if ( $deleted_methods ) {
			$this->deleteClassMethods($class_id, $deleted_methods);
		}
	}

	/**
	 * Deletes methods.
	 *
	 * @param integer $class_id Class ID.
	 * @param array   $methods  Methods.
	 *
	 * @return void
	 */
	protected function deleteClassMethods($class_id, array $methods)
	{
		if ( $methods ) {
			$sql = 'SELECT Id
					FROM ClassMethods
					WHERE ClassId = :class_id AND Name IN (:names)';
			$method_ids = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
				'names' => $methods,
			));
		}
		else {
			$sql = 'SELECT Id
					FROM ClassMethods
					WHERE ClassId = :class_id';
			$method_ids = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
			));
		}

		if ( !$method_ids ) {
			return;
		}

		$sql = 'DELETE FROM ClassMethods WHERE Id IN (:method_ids)';
		$this->db->perform($sql, array('method_ids' => $method_ids));

		$sql = 'DELETE FROM MethodParameters WHERE MethodId IN (:method_ids)';
		$this->db->perform($sql, array('method_ids' => $method_ids));

	}

	/**
	 * Processes method parameters.
	 *
	 * @param integer           $method_id Method ID.
	 * @param \ReflectionMethod $method    Method.
	 *
	 * @return void
	 */
	protected function processClassMethodParameters($method_id, \ReflectionMethod $method)
	{
		$sql = 'SELECT Name
				FROM MethodParameters
				WHERE MethodId = :method_id';
		$old_parameters = $this->db->fetchCol($sql, array(
			'method_id' => $method_id,
		));

		$insert_sql = '	INSERT INTO MethodParameters (MethodId, Name, TypeClass, HasType, TypeName, AllowsNull, IsArray, IsCallable, IsOptional, IsVariadic, CanBePassedByValue, IsPassedByReference, HasDefaultValue, DefaultValue, DefaultConstant)
						VALUES (:method_id, :name, :type_class, :has_type, :type_name, :allows_null, :is_array, :is_callable, :is_optional, :is_variadic, :can_be_passed_by_value, :is_passed_by_reference, :has_default_value, :default_value, :default_constant)';
		$update_sql = '	UPDATE MethodParameters
						SET TypeClass = :type_class, HasType = :has_type, TypeName = :type_name, AllowsNull = :allows_null, IsArray = :is_array, IsCallable = :is_callable, IsOptional = :is_optional, IsVariadic = :is_variadic, CanBePassedByValue = :can_be_passed_by_value, IsPassedByReference = :is_passed_by_reference, HasDefaultValue = :has_default_value, DefaultValue = :default_value, DefaultConstant = :default_constant
						WHERE MethodId = :method_id AND Name = :name';

		$new_parameters = array();

		foreach ( $method->getParameters() as $parameter ) {
			$parameter_name = $parameter->getName();
			$new_parameters[] = $parameter_name;

			$type_class = $parameter->getClass();
			$type_class = $type_class ? $type_class->getName() : null;

			// Doesn't work for parent classes (see https://github.com/goaop/parser-reflection/issues/16).
			$has_type = $parameter->hasType();
			$type_name = $has_type ? (string)$parameter->getType() : null;

			$has_default_value = $parameter->isDefaultValueAvailable();
			$default_value_is_constant = $has_default_value ? $parameter->isDefaultValueConstant() : false;

			$this->db->perform(
				isset($old_parameters[$parameter_name]) ? $update_sql : $insert_sql,
				array(
					'method_id' => $method_id,
					'name' => $parameter_name,
					'type_class' => $type_class,
					'has_type' => (int)$has_type,
					'type_name' => $type_name,
					'allows_null' => (int)$parameter->allowsNull(),
					'is_array' => (int)$parameter->isArray(),
					'is_callable' => (int)$parameter->isCallable(),
					'is_optional' => (int)$parameter->isOptional(),
					'is_variadic' => (int)$parameter->isVariadic(),
					'can_be_passed_by_value' => (int)$parameter->canBePassedByValue(),
					'is_passed_by_reference' => (int)$parameter->isPassedByReference(),
					'has_default_value' => (int)$has_default_value,
					'default_value' => $has_default_value ? json_encode($parameter->getDefaultValue()) : null,
					'default_constant' => $default_value_is_constant ? $parameter->getDefaultValueConstantName() : null,
				)
			);
		}

		$deleted_parameters = array_diff($old_parameters, $new_parameters);

		if ( $deleted_parameters ) {
			$sql = 'DELETE FROM MethodParameters
					WHERE MethodId = :method_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'method_id' => $method_id,
				'names' => $deleted_parameters,
			));
		}
	}

	/**
	 * Returns method scope.
	 *
	 * @param \ReflectionMethod $method Method.
	 *
	 * @return integer
	 */
	protected function getMethodScope(\ReflectionMethod $method)
	{
		if ( $method->isPrivate() ) {
			return self::SCOPE_PRIVATE;
		}

		if ( $method->isProtected() ) {
			return self::SCOPE_PROTECTED;
		}

		return self::SCOPE_PUBLIC;
	}

	/**
	 * Processes raw relations for all classes.
	 *
	 * @return void
	 */
	protected function processClassRawRelations()
	{
		$sql = 'SELECT Id, RawRelations
				FROM Classes
				WHERE RawRelations IS NOT NULL';
		$raw_relations = $this->db->yieldPairs($sql, array(
			'empty_relations' => json_encode(array()),
		));

		foreach ( $raw_relations as $class_id => $class_raw_relations ) {
			$sql = 'SELECT RelatedClass
					FROM ClassRelations
					WHERE ClassId = :class_id';
			$old_class_relations = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
			));

			$new_class_relations = array();

			foreach ( json_decode($class_raw_relations, true) as $class_raw_relation ) {
				list ($related_class, $relation_type, $is_internal) = $class_raw_relation;

				$new_class_relations[] = $this->addRelation(
					$class_id,
					$related_class,
					$relation_type,
					$is_internal,
					$old_class_relations
				);
			}

			$delete_class_relations = array_diff($old_class_relations, $new_class_relations);

			if ( $delete_class_relations ) {
				$sql = 'DELETE FROM ClassRelations
						WHERE ClassId = :class_id AND RelatedClassId IN (:related_class_ids)';
				$this->db->perform($sql, array(
					'class_id' => $class_id,
					'related_class_ids' => $delete_class_relations,
				));
			}
		}

		$sql = 'UPDATE Classes
				SET RawRelations = NULL';
		$this->db->perform($sql);
	}

	/**
	 * Adds a relation.
	 *
	 * @param integer $class_id      Class ID.
	 * @param string  $related_class Related class.
	 * @param integer $relation_type Relation type.
	 * @param boolean $is_internal   Is internal.
	 * @param array   $old_relations Old relations.
	 *
	 * @return string
	 */
	protected function addRelation($class_id, $related_class, $relation_type, $is_internal, array $old_relations)
	{
		$insert_sql = '	INSERT INTO ClassRelations (ClassId, RelatedClass, RelatedClassId, RelationType)
						VALUES (:class_id, :related_class, :related_class_id, :relation_type)';
		$update_sql = ' UPDATE ClassRelations
						SET RelationType = :relation_type
						WHERE ClassId = :class_id AND RelatedClassId = :related_class_id';

		if ( $is_internal ) {
			$related_class_id = 0;
		}
		else {
			$related_class_file = realpath(ReflectionEngine::locateClassFile($related_class));

			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId = :file_id AND Name = :name';
			$related_class_id = $this->db->fetchValue($sql, array(
				'file_id' => $this->processFile($related_class_file),
				'name' => $related_class,
			));
		}

		$this->db->perform(
			in_array($related_class, $old_relations) ? $update_sql : $insert_sql,
			array(
				'class_id' => $class_id,
				'related_class' => $related_class,
				'related_class_id' => $related_class_id,
				'relation_type' => $relation_type,
			)
		);

		return $related_class;
	}

	/**
	 * Determines class locator.
	 *
	 * @return LocatorInterface
	 * @throws \LogicException When file in "class_locator" setting doesn't exist.
	 */
	protected function detectClassLocator()
	{
		$class_locator = null;

		if ( isset($this->config['class_locator']) ) {
			$class_locator_file = $this->resolveProjectPath($this->config['class_locator']);

			if ( !file_exists($class_locator_file) || !is_file($class_locator_file) ) {
				throw new \LogicException(
					'The "' . $this->config['class_locator'] . '" class locator doesn\'t exist.'
				);
			}

			$class_locator = require $class_locator_file;
		}
		else {
			$class_locator_file = $this->resolveProjectPath('vendor/autoload.php');

			if ( file_exists($class_locator_file) && is_file($class_locator_file) ) {
				$class_locator = require $class_locator_file;
			}
		}

		// Make sure memory limit isn't changed by class locator.
		ini_restore('memory_limit');

		if ( is_callable($class_locator) ) {
			return new CallableLocator($class_locator);
		}
		elseif ( $class_locator instanceof ClassLoader ) {
			return new ComposerLocator($class_locator);
		}

		throw new \LogicException(
			'The "class_loader" setting must point to "vendor/autoload.php" or a file, that would return closure.'
		);
	}

	/**
	 * Processes the Finders configuration list.
	 *
	 * @return Finder[]
	 * @throws \LogicException If "finder" setting doesn't exist.
	 * @throws \LogicException If the configured method does not exist.
	 */
	protected function getFinders()
	{
		// Process "finder" config setting.
		if ( !isset($this->config['finder']) ) {
			throw new \LogicException('The "finder" setting must be present in config file.');
		}

		$finders = array();

		foreach ( $this->config['finder'] as $methods ) {
			$finder = Finder::create()->files();

			if ( isset($methods['in']) ) {
				$methods['in'] = (array)$methods['in'];

				foreach ( $methods['in'] as $folder_index => $in_folder ) {
					$methods['in'][$folder_index] = $this->resolveProjectPath($in_folder);
				}
			}

			foreach ( $methods as $method => $arguments ) {
				if ( !method_exists($finder, $method) ) {
					throw new \LogicException(sprintf(
						'The method "Finder::%s" does not exist.',
						$method
					));
				}

				$arguments = (array)$arguments;

				foreach ( $arguments as $argument ) {
					$finder->$method($argument);
				}
			}

			$finders[] = $finder;
		}

		return $finders;
	}

	/**
	 * Resolves path within project.
	 *
	 * @param string $relative_path Relative path.
	 *
	 * @return string
	 */
	protected function resolveProjectPath($relative_path)
	{
		return realpath($this->projectPath . DIRECTORY_SEPARATOR . $relative_path);
	}

	/**
	 * Removes project path from file path.
	 *
	 * @param string $absolute_path Absolute path.
	 *
	 * @return string
	 */
	protected function removeProjectPath($absolute_path)
	{
		return preg_replace($this->projectPathRegExp, '', $absolute_path, 1);
	}

}
