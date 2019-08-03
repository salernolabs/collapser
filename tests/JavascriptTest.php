<?php
namespace SalernoLabs\Tests\Collapser;

use SalernoLabs\Collapser\Javascript;

/**
 * Test cases for Media class
 *
 * @package SalernoLabs
 * @subpackage Collapser
 * @author Eric
 */
class JavascriptTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Ensure exception on empty input
     * @throws \Exception On empty input
     */
    public function testEmptyInput()
    {
        $this->expectException(\Exception::class);
        $collapser = new Javascript();
        $collapser->collapse('');
    }

    /**
     * Test collapse of media
     * @param $input
     * @param $expected
     * @dataProvider dataProviderTestCollapse
     * @throws \Exception But not in this test
     */
    public function testCollapse($input, $expected)
    {
        $collapser = new Javascript();
        $collapser
            ->setDeleteComments(true)
            ->setPreserveNewLines(false)
            ->setDebugMode(false);

        $output = $collapser->collapse($input);

        $this->assertEquals($expected, $output);
    }

    /**
     * Return test data for collapse test
     *
     * @return array
     */
    public function dataProviderTestCollapse()
    {
        $dir = __DIR__ . '/data/javascript/';
        $output = [];

        $i=1;
        while (file_exists($dir . 'test'.$i.'-input.txt'))
        {
            $output[] = [
                file_get_contents($dir . 'test'.$i.'-input.txt'),
                file_get_contents($dir . 'test'.$i.'-output.txt')
            ];
            ++$i;
        }

        return $output;
    }
}
