<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @see https://reposhub.com/php/code-analysis/FriendsOfPHP-PHP-CS-Fixer.html
 */

$header = <<<'EOF'
This file is part of PHP CS Fixer.

(c) Fabien Potencier <fabien@symfony.com>
    Dariusz Rumiński <dariusz.ruminski@gmail.com>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->exclude('tests/Fixtures')
    ->in(__DIR__)
    ->append([
        __DIR__ . '/dev-tools/doc.php',
        // __DIR__.'/php-cs-fixer', disabled, as we want to be able to run bootstrap file even on lower PHP version, to show nice message
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1'                           => true,
        '@PSR12'                          => true,
        '@Symfony'                        => true,
        '@PhpCsFixer'                     => true,
        'blank_line_after_opening_tag'    => true,
        'ordered_imports'                 => true,
        'array_syntax'                    => ['syntax' => 'short'],
        'no_unused_imports'               => true,
        'method_chaining_indentation'     => true,
        'indentation_type'                => true,
        'no_leading_import_slash'         => true,
        'trim_array_spaces'               => true,
        'whitespace_after_comma_in_array' => true,
        'short_scalar_cast'               => true,
        'include'                         => true,
        'no_php4_constructor'             => true,
        'single_quote'                    => true,
        'concat_space'                    => ['spacing' => 'one'],
        'space_after_semicolon'           => true,
        'dir_constant'                    => true,
        'binary_operator_spaces'          => ['default' => 'align_single_space_minimal'],
    ])
    ->setFinder($finder)
;

// special handling of fabbot.io service if it's using too old PHP CS Fixer version
if (false !== getenv('FABBOT_IO')) {
    try {
        PhpCsFixer\FixerFactory::create()
            ->registerBuiltInFixers()
            ->registerCustomFixers($config->getCustomFixers())
            ->useRuleSet(new PhpCsFixer\RuleSet($config->getRules()))
        ;
    } catch (PhpCsFixer\ConfigurationException\InvalidConfigurationException $e) {
        $config->setRules([]);
    } catch (UnexpectedValueException $e) {
        $config->setRules([]);
    } catch (InvalidArgumentException $e) {
        $config->setRules([]);
    }
}

return $config;
