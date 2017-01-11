<?php
namespace Dala00\Upload\Test\TestCase\View\Helper;

use Dala00\Upload\View\Helper\UploadHelper;
use Dala00\Upload\Model\Behavior\UploadBehavior;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;

/**
 * Upload\View\Helper\UploadHelper Test Case
 */
class UploadHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Upload\View\Helper\UploadHelper
     */
    public $Upload;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->Upload = new UploadHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Upload);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testUrl()
    {
        // mock of entity
        $filename = 'test.jpg';
        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['source', 'get'])
            ->getMock();
        $entity->expects($this->once())
            ->method('source')
            ->will($this->returnValue('TestTables'));
        $entity->expects($this->once())
            ->method('get')
            ->will($this->returnValue($filename));

        // mock of table
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getUploadFolder'])
            ->getMock();
        $table->expects($this->once())
            ->method('getUploadFolder')
            ->will($this->returnValue('/path/to/folder/'));
        TableRegistry::set('TestTables', $table);

        // test
        $url = $this->Upload->url($entity, 'test_field');
        $this->assertContains($filename, $url);
    }

    public function testHidden()
    {
        $image = [
            'name' => 'test.jpg',
            'tmp_name' =>  '/tmp/aewoijaefijaw',
            'size' => 123,
            'error' => UPLOAD_ERR_OK,
            'cache' => '09uef09ajwe09fjawoejfawj3fwaef',
        ];

        // mock of entity
        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $entity->expects($this->once())
            ->method('get')
            ->will($this->returnValue($image));

        // test
        $tag = $this->Upload->hidden($entity, 'image_field');
        foreach ($image as $key => $value) {
            $this->assertContains($key, $tag);
        }
    }
}
