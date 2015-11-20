<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api;

/**
 * Builds AWS clients based on configuration settings.
 *
 * @method \Api\ApiGateway\ApiGatewayClient createApiGateway(array $args = [])
 * @method \Api\AutoScaling\AutoScalingClient createAutoScaling(array $args = [])
 * @method \Api\CloudFormation\CloudFormationClient createCloudFormation(array $args = [])
 * @method \Api\CloudFront\CloudFrontClient createCloudFront(array $args = [])
 * @method \Api\CloudHsm\CloudHsmClient createCloudHsm(array $args = [])
 * @method \Api\CloudSearch\CloudSearchClient createCloudSearch(array $args = [])
 * @method \Api\CloudSearchDomain\CloudSearchDomainClient createCloudSearchDomain(array $args = [])
 * @method \Api\CloudTrail\CloudTrailClient createCloudTrail(array $args = [])
 * @method \Api\CloudWatch\CloudWatchClient createCloudWatch(array $args = [])
 * @method \Api\CloudWatchLogs\CloudWatchLogsClient createCloudWatchLogs(array $args = [])
 * @method \Api\CodeCommit\CodeCommitClient createCodeCommit(array $args = [])
 * @method \Api\CodeDeploy\CodeDeployClient createCodeDeploy(array $args = [])
 * @method \Api\CodePipeline\CodePipelineClient createCodePipeline(array $args = [])
 * @method \Api\CognitoIdentity\CognitoIdentityClient createCognitoIdentity(array $args = [])
 * @method \Api\CognitoSync\CognitoSyncClient createCognitoSync(array $args = [])
 * @method \Api\ConfigService\ConfigServiceClient createConfigService(array $args = [])
 * @method \Api\DataPipeline\DataPipelineClient createDataPipeline(array $args = [])
 * @method \Api\DeviceFarm\DeviceFarmClient createDeviceFarm(array $args = [])
 * @method \Api\DirectConnect\DirectConnectClient createDirectConnect(array $args = [])
 * @method \Api\DirectoryService\DirectoryServiceClient createDirectoryService(array $args = [])
 * @method \Api\DynamoDb\DynamoDbClient createDynamoDb(array $args = [])
 * @method \Api\DynamoDbStreams\DynamoDbStreamsClient createDynamoDbStreams(array $args = [])
 * @method \Api\Ec2\Ec2Client createEc2(array $args = [])
 * @method \Api\Ecs\EcsClient createEcs(array $args = [])
 * @method \Api\Efs\EfsClient createEfs(array $args = [])
 * @method \Api\ElastiCache\ElastiCacheClient createElastiCache(array $args = [])
 * @method \Api\ElasticBeanstalk\ElasticBeanstalkClient createElasticBeanstalk(array $args = [])
 * @method \Api\ElasticLoadBalancing\ElasticLoadBalancingClient createElasticLoadBalancing(array $args = [])
 * @method \Api\ElasticTranscoder\ElasticTranscoderClient createElasticTranscoder(array $args = [])
 * @method \Api\ElasticsearchService\ElasticsearchServiceClient createElasticsearchService(array $args = [])
 * @method \Api\Emr\EmrClient createEmr(array $args = [])
 * @method \Api\Firehose\FirehoseClient createFirehose(array $args = [])
 * @method \Api\Glacier\GlacierClient createGlacier(array $args = [])
 * @method \Api\Iam\IamClient createIam(array $args = [])
 * @method \Api\Inspector\InspectorClient createInspector(array $args = [])
 * @method \Api\Iot\IotClient createIot(array $args = [])
 * @method \Api\IotDataPlane\IotDataPlaneClient createIotDataPlane(array $args = [])
 * @method \Api\Kinesis\KinesisClient createKinesis(array $args = [])
 * @method \Api\Kms\KmsClient createKms(array $args = [])
 * @method \Api\Lambda\LambdaClient createLambda(array $args = [])
 * @method \Api\MachineLearning\MachineLearningClient createMachineLearning(array $args = [])
 * @method \Api\MarketplaceCommerceAnalytics\MarketplaceCommerceAnalyticsClient createMarketplaceCommerceAnalytics(array $args = [])
 * @method \Api\OpsWorks\OpsWorksClient createOpsWorks(array $args = [])
 * @method \Api\Rds\RdsClient createRds(array $args = [])
 * @method \Api\Redshift\RedshiftClient createRedshift(array $args = [])
 * @method \Api\Route53\Route53Client createRoute53(array $args = [])
 * @method \Api\Route53Domains\Route53DomainsClient createRoute53Domains(array $args = [])
 * @method \Api\S3\S3Client createS3(array $args = [])
 * @method \Api\Ses\SesClient createSes(array $args = [])
 * @method \Api\Sns\SnsClient createSns(array $args = [])
 * @method \Api\Sqs\SqsClient createSqs(array $args = [])
 * @method \Api\Ssm\SsmClient createSsm(array $args = [])
 * @method \Api\StorageGateway\StorageGatewayClient createStorageGateway(array $args = [])
 * @method \Api\Sts\StsClient createSts(array $args = [])
 * @method \Api\Support\SupportClient createSupport(array $args = [])
 * @method \Api\Swf\SwfClient createSwf(array $args = [])
 * @method \Api\Waf\WafClient createWaf(array $args = [])
 * @method \Api\WorkSpaces\WorkSpacesClient createWorkSpaces(array $args = [])
 */
class Sdk
{
    const VERSION = '0.1.0';

    /** @var array Arguments for creating clients */
    private $args;

    /**
     * Constructs a new SDK object with an associative array of default
     * client settings.
     *
     * @param array $args
     *
     * @throws \InvalidArgumentException
     * @see Api\Sdk::getClient for a list of available options.
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;

        if (!isset($args['handler']) && !isset($args['http_handler'])) {
            $this->args['http_handler'] = default_http_handler();
        }
    }

    public function __call($name, array $args)
    {
        if (strpos($name, 'create') === 0) {
            return $this->createClient(
                substr($name, 6),
                isset($args[0]) ? $args[0] : []
            );
        }

        throw new \BadMethodCallException("Unknown method: {$name}.");
    }

    /**
     * Get a client by name using an array of constructor options.
     *
     * @param string $name Service name or namespace (e.g., DynamoDb, s3).
     * @param array  $args Arguments to configure the client.
     *
     * @return ClientInterface
     * @throws \InvalidArgumentException if any required options are missing or
     *                                   the service is not supported.
     * @see Api\Client::__construct for a list of available options for args.
     */
    public function createClient($name, array $args = [])
    {
        // Get information about the service from the manifest file.
        $service = manifest($name);
        $namespace = $service['namespace'];

        // Merge provided args with stored, service-specific args.
        if (isset($this->args[$namespace])) {
            $args += $this->args[$namespace];
        }

        // Provide the endpoint prefix in the args.
        if (!isset($args['service'])) {
            $args['service'] = $service['endpoint'];
        }

        // Instantiate the client class.
        $client = "Api\\{$namespace}\\{$namespace}Client";
        return new $client($args + $this->args);
    }

    /**
     * Determine the endpoint prefix from a client namespace.
     *
     * @param string $name Namespace name
     *
     * @return string
     * @internal
     * @deprecated Use the `\Api\manifest()` function instead.
     */
    public static function getEndpointPrefix($name)
    {
        return manifest($name)['endpoint'];
    }
}
