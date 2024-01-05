<?php

namespace Drupal\Tests\govcms_security\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;
use Drupal\Tests\rest\Functional\ResourceTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\govcms_security\GovcmsFileConstraintInterface;
use Drupal\rest\RestResourceConfigInterface;
use Drupal\user\Entity\User;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests binary data file upload route.
 */
class RestFileUploadTest extends ResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rest_test',
    'entity_test',
    'file',
    'govcms_security',
  ];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'govcms';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * Entity type ID for this storage.
   *
   * @var string
   */
  protected static string $entityTypeId;

  /**
   * Test file data.
   *
   * @var string
   */
  protected $testFileData = 'GovCMS file uploading test file.';

  /**
   * {@inheritdoc}
   */
  protected static $resourceConfigId = 'file.upload';

  /**
   * The POST URI.
   *
   * @var string
   */
  protected static $postUri = 'file/upload/entity_test/entity_test/field_rest_file_test';

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
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The entity storage for the 'file' entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->fileStorage = $this->container->get('entity_type.manager')
      ->getStorage('file');

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

    // Create an entity that a file can be attached to.
    $this->entity = EntityTest::create([
      'name' => 'Llama',
      'type' => 'entity_test',
    ]);
    $this->entity->setOwnerId(isset($this->account) ? $this->account->id() : 0);
    $this->entity->save();

    // Provision entity_test resource.
    $this->resourceConfigStorage->create([
      'id' => 'entity.entity_test',
      'granularity' => RestResourceConfigInterface::RESOURCE_GRANULARITY,
      'configuration' => [
        'methods' => ['POST'],
        'formats' => [static::$format],
        'authentication' => [static::$auth],
      ],
      'status' => TRUE,
    ])->save();

    // Provisioning the file upload REST resource without the File REST resource
    // does not make sense.
    $this->resourceConfigStorage->create([
      'id' => 'entity.file',
      'granularity' => RestResourceConfigInterface::RESOURCE_GRANULARITY,
      'configuration' => [
        'methods' => ['GET'],
        'formats' => [static::$format],
        'authentication' => isset(static::$auth) ? [static::$auth] : [],
      ],
      'status' => TRUE,
    ])->save();

    $this->refreshTestStateAfterRestConfigChange();
  }

  /**
   * Tests blocked file extension using the file upload POST.
   */
  public function testBlockedFileExtension() {
    $this->initAuthentication();

    $this->provisionResource([static::$format], static::$auth ? [static::$auth] : [], ['POST']);

    $uri = Url::fromUri('base:' . static::$postUri);

    $this->setUpAuthorization('POST');

    // Test blocked file extensions.
    foreach (GovcmsFileConstraintInterface::BLOCKED_EXTENSIONS as $index => $extension) {
      // Test file name.
      $test_file_name = "test$index.$extension";
      // Upload the test file via JSON API.
      /** @var \Psr\Http\Message\ResponseInterface */
      $response = $this->fileRequest($uri, $this->testFileData, ['Content-Disposition' => 'file; filename="' . $test_file_name . '"']);
      // The file upload request should get 403 response.
      $this->assertEquals(403, $response->getStatusCode());
      // The file should not be existed in the public folder.
      $this->assertFileDoesNotExist('public://foobar/' . $test_file_name);
    }

    // Test a txt file that should not be blocked.
    $response = $this->fileRequest($uri, $this->testFileData, ['Content-Disposition' => 'file; filename="example.txt"']);
    $this->assertSame(201, $response->getStatusCode());
    $expected = $this->getExpectedNormalizedEntity();
    $this->assertResponseData($expected, $response);

    // Check the actual file data.
    $this->assertSame($this->testFileData, file_get_contents('public://foobar/example.txt'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {
    return "The following permissions are required: 'administer entity_test content' OR 'administer entity_test_with_bundle content' OR 'create entity_test entity_test_with_bundle entities'.";
  }

  /**
   * Returns the normalized POST entity referencing the uploaded file.
   *
   * @return array
   *   The posted file entity in an array.
   *
   * @see ::testPostFileUpload()
   * @see \Drupal\Tests\rest\Functional\EntityResource\EntityTest\EntityTestResourceTestBase::getNormalizedPostEntity()
   */
  protected function getNormalizedPostEntity() {
    return [
      'type' => [
        [
          'value' => 'entity_test',
        ],
      ],
      'name' => [
        [
          'value' => 'Dramallama',
        ],
      ],
      'field_rest_file_test' => [
        [
          'target_id' => 1,
          'description' => 'The most fascinating file ever!',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options) {
    // The file upload resource only accepts binary data, so there are no
    // normalization edge cases to test, as there are no normalized entity
    // representations incoming.
  }

  /**
   * Gets the expected file entity.
   *
   * @param int $fid
   *   The file ID to load and create normalized data for.
   * @param string $expected_filename
   *   The expected filename for the stored file.
   * @param bool $expected_as_filename
   *   Whether the expected filename should be the filename property too.
   *
   * @return array
   *   The expected normalized data array.
   */
  protected function getExpectedNormalizedEntity($fid = 1, $expected_filename = 'example.txt', $expected_as_filename = FALSE) {
    $author = User::load(static::$auth ? $this->account->id() : 0);
    $file = File::load($fid);

    $expected_normalization = [
      'fid' => [
        [
          'value' => (int) $file->id(),
        ],
      ],
      'uuid' => [
        [
          'value' => $file->uuid(),
        ],
      ],
      'langcode' => [
        [
          'value' => 'en',
        ],
      ],
      'uid' => [
        [
          'target_id' => (int) $author->id(),
          'target_type' => 'user',
          'target_uuid' => $author->uuid(),
          'url' => base_path() . 'user/' . $author->id(),
        ],
      ],
      'filename' => [
        [
          'value' => $expected_as_filename ? $expected_filename : 'example.txt',
        ],
      ],
      'uri' => [
        [
          'value' => 'public://foobar/' . $expected_filename,
          'url' => base_path() . $this->siteDirectory . '/files/foobar/' . rawurlencode($expected_filename),
        ],
      ],
      'filemime' => [
        [
          'value' => 'text/plain',
        ],
      ],
      'filesize' => [
        [
          'value' => strlen($this->testFileData),
        ],
      ],
      'status' => [
        [
          'value' => FALSE,
        ],
      ],
      'created' => [
        [
          'value' => (new \DateTime())->setTimestamp($file->getCreatedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'format' => \DateTime::RFC3339,
        ],
      ],
      'changed' => [
        [
          'value' => (new \DateTime())->setTimestamp($file->getChangedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'format' => \DateTime::RFC3339,
        ],
      ],
    ];

    return $expected_normalization;
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
   *   The response object returned by the file upload request.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function fileRequest(Url $url, $file_contents, array $headers = []) {
    // Set the format for the response.
    $url->setOption('query', ['_format' => static::$format]);

    $request_options = [];
    $headers = $headers + [
      // Set the required (and only accepted) content type for the request.
      'Content-Type' => 'application/octet-stream',
      // Set the required Content-Disposition header for the file name.
      'Content-Disposition' => 'file; filename="example.txt"',
    ];
    $request_options[RequestOptions::HEADERS] = array_filter($headers, function ($value) {
      return $value !== FALSE;
    });
    $request_options[RequestOptions::BODY] = $file_contents;
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions('POST'));

    return $this->request('POST', $url, $request_options);
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
        $this->grantPermissionsToTestedRole(['create entity_test entity_test_with_bundle entities',
          'access content',
        ]);
        break;
    }
  }

  /**
   * Asserts expected normalized data matches response data.
   *
   * @param array $expected
   *   The expected data.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The file upload response.
   */
  protected function assertResponseData(array $expected, ResponseInterface $response) {
    static::recursiveKSort($expected);
    $actual = $this->serializer->decode((string) $response->getBody(), static::$format);
    static::recursiveKSort($actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {
    // There is cacheability metadata to check as file uploads only allows POST
    // requests, which will not return cacheable responses.
    return new CacheableMetadata();
  }

}
