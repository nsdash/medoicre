<?php

declare(strict_types=1);

namespace Mediocre\Shared\Reflection\Finders;

final class ClassFinder
{
  const appRoot = __DIR__ . "/../../../../../../../";

  public function getClassesInNamespace(string $namespace): array
  {
    $files = scandir($this->getNamespaceDirectory($namespace));

    $classes = array_map(function($file) use ($namespace){
      return $namespace . '\\' . str_replace('.php', '', $file);
    }, $files);


    return array_filter($classes, function($possibleClass){
      $className = str_replace('\\\\', '\\', $possibleClass);

      return class_exists($className);
    });
  }

  private function getDefinedNamespaces(): array
  {
    $composerJsonPath = self::appRoot . 'composer.json';
    $composerConfig = json_decode(file_get_contents($composerJsonPath));

    return (array) $composerConfig->autoload->{'psr-4'};
  }

  private function getNamespaceDirectory(string $namespace): string|false
  {
    $composerNamespaces = $this->getDefinedNamespaces();

    $namespaceFragments = explode('\\', $namespace);

    $undefinedNamespaceFragments = [];

    while($namespaceFragments) {
      $possibleNamespace = implode('\\', $namespaceFragments) . '\\';

      if(array_key_exists($possibleNamespace, $composerNamespaces)) {
        return realpath(self::appRoot . $composerNamespaces[$possibleNamespace] . implode('/', $undefinedNamespaceFragments));
      }

      array_unshift($undefinedNamespaceFragments, array_pop($namespaceFragments));
    }

    return false;
  }
}
