<?php

declare(strict_types=1);

/**
 * Generates a static HTML page for GitHub Pages with project overview,
 * test coverage summary, PHPStan details, and Rector configuration.
 */

// -- Gather data --

$composerJson = json_decode(
    file_get_contents(__DIR__ . '/../../composer.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);

$projectName = $composerJson['name'] ?? 'Unknown';
$description = $composerJson['description'] ?? '';
$license = $composerJson['license'] ?? 'Unknown';
$phpVersion = $composerJson['require']['php'] ?? 'Unknown';

// PHPUnit coverage summary (max 10 lines)
$coverageLines = [];
$phpunitOutput = @file_get_contents(__DIR__ . '/../../build/phpunit-output.txt');
if ($phpunitOutput !== false) {
    // Test result summary line
    if (preg_match('/^(OK|FAILURES|ERRORS).*$/m', $phpunitOutput, $match)) {
        $coverageLines[] = trim($match[0]);
    }

    // Coverage summary lines (Classes, Methods, Lines percentages)
    if (preg_match_all('/^\s*(Classes|Methods|Functions|Lines)\s*:.*$/m', $phpunitOutput, $matches)) {
        foreach ($matches[0] as $line) {
            $coverageLines[] = trim($line);
        }
    }

    $coverageLines = array_slice($coverageLines, 0, 10);
}
$coverageSummary = implode("\n", $coverageLines);

// PHPStan
$phpstanLevel = 7;
$phpstanPaths = ['bin/', 'config/', 'public/', 'src/', 'tests/'];
$phpstanComplexityClass = 50;
$phpstanComplexityFunction = 8;

$phpstanErrors = 0;
$phpstanJsonOutput = @file_get_contents(__DIR__ . '/../../build/phpstan-output.json');
if ($phpstanJsonOutput !== false) {
    $phpstanData = json_decode($phpstanJsonOutput, true);
    if (isset($phpstanData['totals']['file_errors'])) {
        $phpstanErrors = (int)$phpstanData['totals']['file_errors'];
    }
}

// Parse baseline errors
$baselineErrors = [];
$baselineTotal = 0;
$baselineContent = @file_get_contents(__DIR__ . '/../../phpstan-baseline.neon');
if ($baselineContent !== false) {
    if (preg_match_all('/identifier:\s*(.+)$/m', $baselineContent, $matches)) {
        foreach ($matches[1] as $identifier) {
            $identifier = trim($identifier);
            $baselineErrors[$identifier] = ($baselineErrors[$identifier] ?? 0) + 1;
            $baselineTotal++;
        }
        arsort($baselineErrors);
    }
}

// Rector config summary
$rectorSets = ['deadCode', 'codeQuality', 'codingStyle', 'earlyReturn', 'symfonyConfigs'];
$rectorComposerBased = ['twig', 'doctrine', 'phpunit', 'symfony'];
$rectorSymfonySets = ['SYMFONY_CODE_QUALITY', 'SYMFONY_CONSTRUCTOR_INJECTION'];
$rectorAdditionalRules = ['AddVoidReturnTypeWhereNoReturnRector'];

$rectorOutput = @file_get_contents(__DIR__ . '/../../build/rector-output.txt');
$rectorClean = false;
if ($rectorOutput !== false) {
    $rectorClean = str_contains($rectorOutput, '[OK]')
        || str_contains($rectorOutput, 'Rector is done!');
}

// -- Generate HTML --

$date = date('Y-m-d H:i:s T');

$baselineHtml = '';
if ($baselineErrors !== []) {
    $baselineHtml .= '<table class="table table-sm table-dark table-striped">';
    $baselineHtml .= '<thead><tr><th>Error Identifier</th><th class="text-end">Count</th></tr></thead><tbody>';
    foreach ($baselineErrors as $identifier => $count) {
        $baselineHtml .= sprintf(
            '<tr><td><code>%s</code></td><td class="text-end">%d</td></tr>',
            htmlspecialchars($identifier),
            $count,
        );
    }
    $baselineHtml .= '</tbody></table>';
}

$coverageHtml = '';
if ($coverageSummary !== '') {
    $coverageHtml = '<pre class="p-3 rounded" style="background-color:#1a1d21;color:#e0e0e0;">' . htmlspecialchars(trim($coverageSummary)) . '</pre>';
} else {
    $coverageHtml = '<p class="text-secondary">No coverage data available.</p>';
}

$rectorSetsHtml = '';
foreach ($rectorSets as $set) {
    $rectorSetsHtml .= sprintf('<span class="badge bg-primary me-1 mb-1">%s</span>', htmlspecialchars($set));
}

$rectorComposerHtml = '';
foreach ($rectorComposerBased as $set) {
    $rectorComposerHtml .= sprintf('<span class="badge bg-info text-dark me-1 mb-1">%s</span>', htmlspecialchars($set));
}

$rectorSymfonySetsHtml = '';
foreach ($rectorSymfonySets as $set) {
    $rectorSymfonySetsHtml .= sprintf('<span class="badge bg-success me-1 mb-1">%s</span>', htmlspecialchars($set));
}

$rectorRulesHtml = '';
foreach ($rectorAdditionalRules as $rule) {
    $rectorRulesHtml .= sprintf('<span class="badge bg-secondary me-1 mb-1">%s</span>', htmlspecialchars($rule));
}

$rectorStatusBadge = $rectorClean
    ? '<span class="badge bg-success">Clean</span>'
    : '<span class="badge bg-warning text-dark">Changes suggested</span>';

$phpstanStatusBadge = $phpstanErrors === 0
    ? '<span class="badge bg-success">No errors</span>'
    : sprintf('<span class="badge bg-danger">%d error%s</span>', $phpstanErrors, $phpstanErrors > 1 ? 's' : '');

$paths = htmlspecialchars(implode(', ', $phpstanPaths));

$html = <<<HTML
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$projectName} - Project Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #111318; }
        .hero { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; padding: 3rem 0; }
        .card { border: 1px solid #2a2d35; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1 class="display-5 fw-bold">{$projectName}</h1>
            <p class="lead mb-1">{$description}</p>
            <small>License: {$license} | PHP {$phpVersion}</small>
        </div>
    </div>

    <div class="container my-4">
        <div class="row g-4">
            <!-- Test Coverage -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">Test Coverage</h3>
                        {$coverageHtml}
                        <a href="coverage/index.html" class="btn btn-sm btn-outline-primary mt-2">Full Coverage Report</a>
                    </div>
                </div>
            </div>

            <!-- PHPStan -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">PHPStan {$phpstanStatusBadge}</h3>
                        <ul class="list-unstyled">
                            <li><strong>Level:</strong> {$phpstanLevel}</li>
                            <li><strong>Paths:</strong> <code>{$paths}</code></li>
                            <li><strong>Cognitive Complexity:</strong> class &le; {$phpstanComplexityClass}, function &le; {$phpstanComplexityFunction}</li>
                            <li><strong>Baseline errors:</strong> {$baselineTotal}</li>
                        </ul>
                        <h5>Baseline Summary</h5>
                        {$baselineHtml}
                    </div>
                </div>
            </div>

            <!-- Rector -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Rector {$rectorStatusBadge}</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Prepared Sets</h5>
                                <p>{$rectorSetsHtml}</p>
                            </div>
                            <div class="col-md-4">
                                <h5>Composer-based</h5>
                                <p>{$rectorComposerHtml}</p>
                            </div>
                            <div class="col-md-4">
                                <h5>Symfony Sets</h5>
                                <p>{$rectorSymfonySetsHtml}</p>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <h5>Attribute Sets</h5>
                                <p><span class="badge bg-light text-dark">all</span></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Additional Rules</h5>
                                <p>{$rectorRulesHtml}</p>
                            </div>
                            <div class="col-md-4">
                                <h5>Paths</h5>
                                <p><code>src/</code>, <code>tests/</code> + root files</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-secondary mt-4 mb-3">
            <small>Generated on {$date}</small>
        </footer>
    </div>
</body>
</html>
HTML;

// Write output
$buildDir = __DIR__ . '/../../build';
if (!is_dir($buildDir)) {
    mkdir($buildDir, 0o755, true);
}

file_put_contents($buildDir . '/index.html', $html);

echo "Generated build/index.html successfully.\n";
