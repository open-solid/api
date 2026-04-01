<?php

declare(strict_types=1);

namespace OpenSolid\Api\Command;

use OpenSolid\Api\OpenApi\OpenApiGenerator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'openapi:generate',
    description: 'Generate the OpenAPI specification file',
)]
final readonly class GenerateOpenApiCommand
{
    public function __construct(
        private OpenApiGenerator $generator,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,

        #[Argument('Output file path', name: 'output')]
        string $outputPath = 'openapi.json',
    ): int {
        $openApi = $this->generator->generate();

        $format = pathinfo($outputPath, PATHINFO_EXTENSION);
        $content = match ($format) {
            'yaml', 'yml' => $openApi->toYaml(),
            default => $openApi->toJson(),
        };

        file_put_contents($outputPath, $content);

        $io->success(sprintf('OpenAPI spec written to %s', $outputPath));

        return Command::SUCCESS;
    }
}
