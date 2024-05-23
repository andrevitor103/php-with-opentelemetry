<?php

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use Monolog\Logger;
use OpenTelemetry\API\Globals;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Psr\Log\LogLevel;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$loggerProvider = Globals::loggerProvider();
$handler = new Handler(
    $loggerProvider,
    LogLevel::INFO
);
$monolog = new Logger('otel-php-monolog', [$handler]);

$transport = PsrTransportFactory::discover()
    ->create('http://zipkin:9411/api/v2/spans', 'application/json');
$zipkinExporter = new Exporter($transport);
$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor($zipkinExporter)
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');


$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) use ($monolog, $tracer) {
    $span = $tracer->spanBuilder('begin')->startSpan();
    $result = random_int(1,6);
    random_int(1,6);
    $span->end();
    $span = $tracer->spanBuilder('request')->startSpan();
    $client = new Client();
    $resultApi = $client->get('https://api.adviceslip.com/advice');
    $span->setStatus(StatusCodeInterface::STATUS_OK);
    $span->end();
    $response->getBody()->write(strval($result));
    $monolog->info('dice rolled', ['result' => $result]);
    $monolog->info('que massa', ['test' => $resultApi->getBody()]);
    return $response;
});

$app->run();
