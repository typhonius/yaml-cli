<?php

namespace Grasmash\YamlCli\Tests\Command;

use Dflydev\DotAccessData\Data;
use Grasmash\YamlCli\Command\UnsetKeyCommand;
use Grasmash\YamlCli\Tests\TestBase;
use Symfony\Component\Console\Tester\CommandTester;

class UnsetKeyCommandTest extends TestBase
{

    /** @var string */
    protected $temp_file;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Make a temporary copy of good.yml so that we can update a value
        // without destroying the original.
        $source = __DIR__ . '/../resources/good.yml';
        $this->temp_file = __DIR__ . '/../resources/temp.yml';
        if (file_exists($source)) {
            copy($source, $this->temp_file);
        }
    }

    /**
     * Removes temporary file.
     */
    public function tearDown() {
        parent::tearDown();

        unlink($this->temp_file);
    }

    /**
     * Tests the 'update:key' command.
     *
     * @dataProvider getValueProvider
     */
    public function testUpdateKey($filename, $key, $expected) {
        $this->application->add(new UnsetKeyCommand());

        /** @var UnsetKeyCommand $command */
        $command = $this->application->find('unset:key');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'filename' => $filename,
            'key' => $key
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains($expected, $output);

        // If the command was successful, also make sure that key was actually
        // unset. This conditional is necessary because we
        // pass in a "missing" file as part of the test data set.
        if ($commandTester->getStatusCode() == 0) {
            $contents = $command->loadYamlFile($filename);
            $data = new Data($contents);
            $this->assertNotTrue($data->has($key), "The file $filename contains the old key $key. It should not.");
        }
    }

    /**
     * Provides values to testUpdateKey().
     *
     * @return array
     *   An array of values to test.
     */
    public function getValueProvider()
    {

        $filename = 'tests/resources/temp.yml';

        return [
            [$filename, 'deep-array.second.third.fourth', "The key 'deep-array.second.third.fourth' was removed from $filename."],
            ['missing.yml', 'not-real', "The file missing.yml does not exist."],
        ];
    }
}