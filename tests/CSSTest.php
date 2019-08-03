<?php
namespace SalernoLabs\Tests\Collapser;

use SalernoLabs\Collapser\CSS;

/**
 * Test cases for Media class
 *
 * @package SalernoLabs
 * @subpackage Collapser
 * @author Eric
 */
class CSSTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test collapse of media
     *
     * @param $input
     * @param $expected
     * @dataProvider dataProviderTestCollapse
     * @throws \Exception But not in this test
     */
    public function testCollapse($input, $expected)
    {
        $collapser = new CSS();
        $collapser->setDeleteComments(true);

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
        $dir = __DIR__ . '/data/css/';
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
