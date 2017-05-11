<?php
/**
 * Test cases for Media class
 *
 * @package SalernoLabs
 * @subpackage Collapser
 * @author Eric
 */
namespace SalernoLabs\Tests\Collapser;

class MediaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test collapse of media
     *
     * @param $input
     * @param $expected
     * @dataProvider dataProviderTestCollapse
     */
    public function testCollapse($input, $expected)
    {
        $collapser = new \SalernoLabs\Collapser\Media();

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
        $dir = __DIR__ . '/data/media/';
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