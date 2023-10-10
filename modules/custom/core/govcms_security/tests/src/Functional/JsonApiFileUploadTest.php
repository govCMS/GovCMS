<?php

namespace Drupal\Tests\govcms_security\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Tests\ApiRequestTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\govcms_security\GovcmsFileConstraintInterface;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests file upload via JSON API.
 *
 * @group jsonapi
 */
class JsonApiFileUploadTest extends BrowserTestBase {

  use ApiRequestTrait {
    makeApiRequest as request;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'jsonapi',
    'entity_test',
    'file',
    'jsonapi_test_field_access',
    'rest_test',
    'basic_auth',
    'text',
    'govcms_security',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * @see $entity
   */
  protected static $entityTypeId = 'entity_test';

  /**
   * {@inheritdoc}
   *
   * @see $entity
   */
  protected static $resourceTypeName = 'entity_test--entity_test';

  /**
   * The POST URI.
   *
   * @var string
   */
  protected static $postUri = '/jsonapi/entity_test/entity_test/field_rest_file_test';

  /**
   * Test file data.
   *
   * @var string
   */
  protected $testFileData = 'GovCMS test file Data.';

  /**
   * The test field storage config.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field config.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The parent entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The account to use for authentication.
   *
   * @var null|\Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an account, which tests will use. Also ensure the @current_user
    // service this account, to ensure certain access check logic in tests works
    // as expected.
    $this->account = $this->createUser();
    $this->container->get('current_user')->setAccount($this->account);

    // Create an entity.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->entityStorage = $entity_type_manager->getStorage(static::$entityTypeId);
    $this->entity = $this->setUpFields($this->createEntity(), $this->account);

    $this->resourceType = $this->container->get('jsonapi.resource_type.repository')->getByTypeName(static::$resourceTypeName);

    // Add a file field.
    $this->fieldStorage = FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_rest_file_test',
      'type' => 'file',
      'settings' => [
        'uri_scheme' => 'public',
      ],
    ])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_rest_file_test',
      'bundle' => 'entity_test',
      'settings' => [
        'file_directory' => 'foobar',
        'file_extensions' => 'txt',
        'max_filesize' => '',
      ],
    ])
      ->setLabel('Test file field')
      ->setTranslatable(FALSE);
    $this->field->save();

    // Reload entity so that it has the new field.
    $this->entity = $this->entityStorage->loadUnchanged($this->entity->id());

    $this->rebuildAll();
  }

  /**
   * Tests using the file upload POST route with blocked extensions.
   */
  public function testFileUploadBlockedExtension() {
    $uri = Url::fromUri('base:' . static::$postUri);
    // Allow to upload file via JSON API.
    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);
    // Set user permission.
    $this->setUpAuthorization('POST');

    // Test files should be blocked due to their extensions.
    foreach (GovcmsFileConstraintInterface::BLOCKED_EXTENSIONS as $index => $extension) {
      // Change the field setting to allow the blocked extension.
      $this->field->setSetting('file_extensions', $extension)->save();
      // Test file name.
      $test_file_name = "test$index.$extension";

      // Upload the test file via JSON API.
      /** @var \Psr\Http\Message\ResponseInterface */
      $response = $this->fileRequest($uri, $this->testFileData, ['Content-Disposition' => 'filename="' . $test_file_name . '"']);
      // The file upload request should get 403 response.
      $this->assertEquals(403, $response->getStatusCode());
      // The file should not be existed in the public folder.
      $this->assertFileDoesNotExist('public://foobar/' . $test_file_name);
    }

    // Test file that should not be blocked.
    // Test uploading an image file.
    $test_file_name = "test.png";
    // Change the field setting to allow the blocked extension.
    $this->field->setSetting('file_extensions', 'png')->save();
    /** @var \Psr\Http\Message\ResponseInterface */
    $response = $this->fileRequest($uri, $this->testFileData, ['Content-Disposition' => 'filename="' . $test_file_name . '"']);
    // Expected JSON response.
    $expected = $this->getExpectedDocument(1, $test_file_name, TRUE);
    // Override the expected filesize.
    $expected['data']['attributes']['filesize'] = strlen($this->testFileData);
    // The file mime should be 'image/png'.
    $expected['data']['attributes']['filemime'] = 'image/png';
    $this->assertResponseData($expected, $response);
    // The file should be uploaded to the public folder.
    $this->assertFileExists('public://foobar/' . $test_file_name);
  }

  /**
   * Performs a file upload request. Wraps the Guzzle HTTP client.
   *
   * @param \Drupal\Core\Url $url
   *   URL to request.
   * @param string $file_contents
   *   The file contents to send as the request body.
   * @param array $headers
   *   Additional headers to send with the request. Defaults will be added for
   *   Content-Type and Content-Disposition. In order to remove the defaults set
   *   the header value to FALSE.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The received response.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function fileRequest(Url $url, $file_contents, array $headers = []) {
    $request_options = [];
    $headers = $headers + [
      // Set the required (and only accepted) content type for the request.
      'Content-Type' => 'application/octet-stream',
      // Set the required JSON:API Accept header.
      'Accept' => 'application/vnd.api+json',
    ];
    $request_options[RequestOptions::HEADERS] = array_filter($headers, function ($value) {
      return $value !== FALSE;
    });
    $request_options[RequestOptions::BODY] = $file_contents;
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    return $this->request('POST', $url, $request_options);
  }

  /**
   * Returns the expected JSON:API document for the expected file entity.
   *
   * @param int $fid
   *   The file ID to load and create a JSON:API document for.
   * @param string $expected_filename
   *   The expected filename for the stored file.
   * @param bool $expected_as_filename
   *   Whether the expected filename should be the filename property too.
   * @param bool $expected_status
   *   The expected file status. Defaults to FALSE.
   *
   * @return array
   *   A JSON:API response document.
   */
  protected function getExpectedDocument($fid = 1, $expected_filename = 'example.txt', $expected_as_filename = FALSE, $expected_status = FALSE) {
    $author = User::load($this->account->id());
    $file = File::load($fid);
    $self_url = Url::fromUri('base:/jsonapi/file/file/' . $file->uuid())->setAbsolute()->toString(TRUE)->getGeneratedUrl();

    return [
      'jsonapi' => [
        'meta' => [
          'links' => [
            'self' => ['href' => 'http://jsonapi.org/format/1.0/'],
          ],
        ],
        'version' => '1.0',
      ],
      'links' => [
        'self' => ['href' => $self_url],
      ],
      'data' => [
        'id' => $file->uuid(),
        'type' => 'file--file',
        'links' => [
          'self' => ['href' => $self_url],
        ],
        'attributes' => [
          'created' => (new \DateTime())->setTimestamp($file->getCreatedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'changed' => (new \DateTime())->setTimestamp($file->getChangedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'filemime' => 'text/plain',
          'filename' => $expected_as_filename ? $expected_filename : 'example.txt',
          'filesize' => strlen($this->testFileData),
          'langcode' => 'en',
          'status' => $expected_status,
          'uri' => [
            'value' => 'public://foobar/' . $expected_filename,
            'url' => base_path() . $this->siteDirectory . '/files/foobar/' . rawurlencode($expected_filename),
          ],
          'drupal_internal__fid' => (int) $file->id(),
        ],
        'relationships' => [
          'uid' => [
            'data' => [
              'id' => $author->uuid(),
              'meta' => [
                'drupal_internal__target_id' => (int) $author->id(),
              ],
              'type' => 'user--user',
            ],
            'links' => [
              'related' => ['href' => $self_url . '/uid'],
              'self' => ['href' => $self_url . '/relationships/uid'],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Returns Guzzle request options for authentication.
   *
   * @return array
   *   Guzzle request options to use for authentication.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function getAuthenticationRequestOptions() {
    return [
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode($this->account->name->value . ':' . $this->account->passRaw),
      ],
    ];
  }

  /**
   * Sets up additional fields for testing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The primary test entity.
   * @param \Drupal\user\UserInterface $account
   *   The primary test user account.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The reloaded entity with the new fields attached.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpFields(EntityInterface $entity, UserInterface $account) {
    if (!$entity instanceof FieldableEntityInterface) {
      return $entity;
    }

    $entity_bundle = $entity->bundle();

    // Add access-protected field.
    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test',
      'type' => 'text',
    ])
      ->setCardinality(1)
      ->save();
    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test',
      'bundle' => $entity_bundle,
    ])
      ->setLabel('Test field')
      ->setTranslatable(FALSE)
      ->save();

    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_jsonapi_test_entity_ref',
      'type' => 'entity_reference',
    ])
      ->setSetting('target_type', 'user')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->save();

    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_jsonapi_test_entity_ref',
      'bundle' => $entity_bundle,
    ])
      ->setTranslatable(FALSE)
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => NULL,
      ])
      ->save();

    // Add multi-value field.
    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test_multivalue',
      'type' => 'string',
    ])
      ->setCardinality(3)
      ->save();
    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test_multivalue',
      'bundle' => $entity_bundle,
    ])
      ->setLabel('Test field: multi-value')
      ->setTranslatable(FALSE)
      ->save();

    \Drupal::service('router.builder')->rebuildIfNeeded();

    // Reload entity so that it has the new field.
    $reloaded_entity = $this->entityLoadUnchanged($entity->id());
    // Some entity types are not stored, hence they cannot be reloaded.
    if ($reloaded_entity !== NULL) {
      $entity = $reloaded_entity;

      // Set a default value on the fields.
      $entity->set('field_rest_test', ['value' => 'All the faith he had had had had no effect on the outcome of his life.']);
      $entity->set('field_jsonapi_test_entity_ref', ['user' => $account->id()]);
      $entity->set('field_rest_test_multivalue', [[
        'value' => 'One',
      ],
        ['value' => 'Two'],
      ]);
      $entity->save();
    }

    return $entity;
  }

  /**
   * Loads an entity in the test container, ignoring the static cache.
   *
   * @param int $id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity.
   *
   * @todo Remove this after https://www.drupal.org/project/drupal/issues/3038706 lands.
   */
  protected function entityLoadUnchanged($id) {
    $this->entityStorage->resetCache();
    return $this->entityStorage->loadUnchanged($id);
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    // Create an entity that a file can be attached to.
    $entity_test = EntityTest::create([
      'name' => 'Llama',
      'type' => 'entity_test',
    ]);
    $entity_test->setOwnerId($this->account->id());
    $entity_test->save();

    return $entity_test;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'GET':
        $this->grantPermissionsToTestedRole(['view test entity']);
        break;

      case 'POST':
        $this->grantPermissionsToTestedRole([
          'create entity_test entity_test_with_bundle entities',
          'access content',
        ]);
        break;

      case 'PATCH':
        $this->grantPermissionsToTestedRole([
          'administer entity_test content',
          'access content',
        ]);
        break;
    }
  }

  /**
   * Grants permissions to the authenticated role.
   *
   * @param string[] $permissions
   *   Permissions to grant.
   */
  protected function grantPermissionsToTestedRole(array $permissions) {
    $role = Role::load(RoleInterface::AUTHENTICATED_ID);
    foreach ($permissions as $permission) {
      $role->grantPermission($permission);
    }
    $role->trustData()->save();
  }

  /**
   * Asserts expected normalized data matches response data.
   *
   * @param array $expected
   *   The expected data.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The file upload response.
   *
   * @internal
   */
  protected function assertResponseData(array $expected, ResponseInterface $response): void {
    static::recursiveKSort($expected);
    $actual = Json::decode((string) $response->getBody());
    static::recursiveKSort($actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * Recursively sorts an array by key.
   *
   * @param array $array
   *   An array to sort.
   */
  protected static function recursiveKsort(array &$array) {
    // First, sort the main array.
    ksort($array);

    // Then check for child arrays.
    foreach ($array as $key => &$value) {
      if (is_array($value)) {
        static::recursiveKsort($value);
      }
    }
  }

}
