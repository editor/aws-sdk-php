<?php
/**
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Aws\Glacier;

use Aws\Common\Client\AbstractClient;
use Aws\Common\Client\ClientBuilder;
use Aws\Common\Enum\ClientOptions as Options;
use Aws\Common\Exception\Parser\JsonRestExceptionParser;
use Aws\Common\Signature\SignatureV4;
use Guzzle\Common\Collection;
use Guzzle\Service\Resource\Model;
use Guzzle\Service\Resource\MapResourceIteratorFactory;

/**
 * Client to interact with Amazon Glacier
 *
 * @method Model abortMultipartUpload(array $args = array()) {@command glacier AbortMultipartUpload}
 * @method Model completeMultipartUpload(array $args = array()) {@command glacier CompleteMultipartUpload}
 * @method Model createVault(array $args = array()) {@command glacier CreateVault}
 * @method Model deleteArchive(array $args = array()) {@command glacier DeleteArchive}
 * @method Model deleteVault(array $args = array()) {@command glacier DeleteVault}
 * @method Model deleteVaultNotifications(array $args = array()) {@command glacier DeleteVaultNotifications}
 * @method Model describeJob(array $args = array()) {@command glacier DescribeJob}
 * @method Model describeVault(array $args = array()) {@command glacier DescribeVault}
 * @method Model getJobOutput(array $args = array()) {@command glacier GetJobOutput}
 * @method Model getVaultNotifications(array $args = array()) {@command glacier GetVaultNotifications}
 * @method Model initiateJob(array $args = array()) {@command glacier InitiateJob}
 * @method Model initiateMultipartUpload(array $args = array()) {@command glacier InitiateMultipartUpload}
 * @method Model listJobs(array $args = array()) {@command glacier ListJobs}
 * @method Model listMultipartUploads(array $args = array()) {@command glacier ListMultipartUploads}
 * @method Model listParts(array $args = array()) {@command glacier ListParts}
 * @method Model listVaults(array $args = array()) {@command glacier ListVaults}
 * @method Model setVaultNotifications(array $args = array()) {@command glacier SetVaultNotifications}
 * @method Model uploadArchive(array $args = array()) {@command glacier UploadArchive}
 * @method Model uploadMultipartPart(array $args = array()) {@command glacier UploadMultipartPart}
 */
class GlacierClient extends AbstractClient
{
    /**
     * @inheritdoc
     */
    protected $directory = __DIR__;

    /**
     * Factory method to create a new Amazon Glacier client using an array of configuration options:
     *
     * Credential options (`key`, `secret`, and optional `token` OR `credentials` is required)
     *
     * - key: AWS Access Key ID
     * - secret: AWS secret access key
     * - credentials: You can optionally provide a custom `Aws\Common\Credentials\CredentialsInterface` object
     * - token: Custom AWS security token to use with request authentication
     * - token.ttd: UNIX timestamp for when the custom credentials expire
     * - credentials.cache: Used to cache credentials when using providers that require HTTP requests. Set the true
     *   to use the default APC cache or provide a `Guzzle\Common\Cache\CacheAdapterInterface` object.
     * - credentials.cache.key: Optional custom cache key to use with the credentials
     * - credentials.client: Pass this option to specify a custom `Guzzle\Http\ClientInterface` to use if your
     *   credentials require a HTTP request (e.g. RefreshableInstanceProfileCredentials)
     *
     * Region and Endpoint options (a `region` and optional `scheme` OR a `base_url` is required)
     *
     * - region: Region name (e.g. 'us-east-1', 'us-west-1', 'us-west-2', 'eu-west-1', etc...)
     * - scheme: URI Scheme of the base URL (e.g. 'https', 'http').
     * - base_url: Instead of using a `region` and `scheme`, you can specify a custom base URL for the client
     * - endpoint_provider: Optional `Aws\Common\Region\EndpointProviderInterface` used to provide region endpoints
     *
     * Generic client options
     *
     * - ssl.cert: Set to true to use the bundled CA cert or pass the full path to an SSL certificate bundle. This
     *   option should be used when you encounter curl error code 60.
     * - curl.options: Array of cURL options to apply to every request.
     *   See http://www.php.net/manual/en/function.curl-setopt.php for a list of available options
     * - signature: You can optionally provide a custom signature implementation used to sign requests
     * - signature.service: Set to explicitly override the service name used in signatures
     * - signature.region:  Set to explicitly override the region name used in signatures
     * - client.backoff.logger: `Guzzle\Common\Log\LogAdapterInterface` object used to log backoff retries. Use
     *   'debug' to emit PHP warnings when a retry is issued.
     * - client.backoff.logger.template: Optional template to use for exponential backoff log messages. See
     *   `Guzzle\Http\Plugin\ExponentialBackoffLogger` for formatting information.
     *
     * @param array|Collection $config Client configuration data
     *
     * @return self
     */
    public static function factory($config = array())
    {
        // Setup Glacier client
        $client = ClientBuilder::factory(__NAMESPACE__)
            ->setConfig($config)
            ->setConfigDefaults(array(
                // General service configuration
                Options::SERVICE => 'glacier',
                Options::SCHEME  => 'https',
                // Set default value for "accountId" for all requests
                'command.params' => array(
                    'accountId'               => '-',
                    Options::MODEL_PROCESSING => true
                )
            ))
            ->setSignature(new SignatureV4())
            ->setExceptionParser(new JsonRestExceptionParser())
            ->build();

        // Add the Glacier version header required for all operations
        $client->setDefaultHeaders(array(
            'x-amz-glacier-version' => $client->getDescription()->getApiVersion()
        ));

        // Use the same iterator class for every iterator
        $client->setResourceIteratorFactory(new MapResourceIteratorFactory(array(
             '*' => 'Aws\Glacier\Iterator\DefaultIterator'
        )));

        // Listen for upload operations and make sure the required hash headers are added
        $client->addSubscriber(new GlacierUploadListener());

        return $client;
    }
}
