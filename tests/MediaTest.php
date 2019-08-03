<?php
namespace SalernoLabs\Tests\Collapser;

use SalernoLabs\Collapser\Media;

/**
 * Test cases for Media class
 *
 * @package SalernoLabs
 * @subpackage Collapser
 * @author Eric
 */
class MediaTest extends \PHPUnit\Framework\TestCase
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
        $collapser = new Media();

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

    /**
     * Test debug mode
     * @throws \Exception
     */
    public function testDebugMode()
    {
        $collapser = new Media();
        $collapser
            ->setDebugMode(true);

        $output = $collapser->collapse('test{ blargh: true   }');

        $this->assertRegExp('#\/\* culled 5 chars in [0-9.]+#', $output);
    }

    /**
     * Test unclosed comment
     * @throws \Exception On invalid input
     */
    public function testUnclosedComment()
    {
        $this->expectException(\Exception::class);
        $collapser = new Media();
        $collapser->collapse('var l = 4 / 1; /* hello');
    }

    /**
     * @throws \Exception On invalid input
     */
    public function testPreserveNewLines()
    {
        $collapser = new Media();
        $collapser->setPreserveNewLines(true);

        $test = 'blargh' . "\n" . 'blargh' . "\n\n\na";
        $output = $collapser->collapse($test);

        $expected = $test = 'blargh' . "\n" . 'blargh' . "\na";
        $this->assertSame($expected, $output);
    }

    /**
     * @throws \Exception On invalid input
     */
    public function testDontDeleteComments()
    {
        $collapser = new Media();
        $collapser->setDeleteComments(false);

        $test = '/* fun */';
        $output = $collapser->collapse($test);

        $this->assertSame($test, $output);
    }
}
