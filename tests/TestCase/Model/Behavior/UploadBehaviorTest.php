<?php
namespace Dala00\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use Dala00\Upload\Model\Behavior\UploadBehavior;
use Dala00\Upload\File\DebugFileSystem;

/**
 * Upload\Model\Behavior\UploadBehavior Test Case
 */
class UploadBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Upload\Model\Behavior\UploadBehavior
     */
    public $Upload;
    public $Table;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->schema = $this->getMockBuilder('Schema')
            ->disableOriginalConstructor()
            ->setMethods(['columnType'])
            ->getMock();
        $methods = get_class_methods('Cake\ORM\Table');
        $this->Table = $this->getMockBuilder('Cake\ORM\Table')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        $this->Table->expects($this->any())
            ->method('schema')
            ->will($this->returnValue($this->schema));
        $this->defaultConfig = [
            'fields' => [
                'test' => ['path' => 'webroot{DS}files{DS}{primaryKey}{DS}{field}{DS}'],
            ],
        ];
        $this->Upload = new UploadBehavior($this->Table, $this->defaultConfig);
        $this->Upload->fileSystem(new DebugFileSystem);

        $this->dataOk = [
            'field' => [
                'tmp_name' => 'path/to/file',
                'name' => 'derp',
                'error' => UPLOAD_ERR_OK,
                'size' => 1,
                'type' => 'text',
            ]
        ];
        $this->dataError = [
            'field' => [
                'tmp_name' => 'path/to/file',
                'name' => 'derp',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
                'type' => '',
            ]
        ];
        $this->settings = ['fields' => ['field' => []]];
        $this->behaviorMethods = get_class_methods('Dala00\Upload\Model\Behavior\UploadBehavior');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Upload);
        unset($this->Table);

        parent::tearDown();
    }

    public function testBeforeMarshalOk()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')
            ->disableOriginalConstructor()
            ->setMethods(['isEmptyAllowed'])
            ->getMock();
        $validator->expects($this->once())
            ->method('isEmptyAllowed')
            ->will($this->returnValue(true));

        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->disableOriginalConstructor()
            ->setMethods(['validator'])
            ->getMock();
        $table->expects($this->once())
            ->method('validator')
            ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Dala00\Upload\Model\Behavior\UploadBehavior')
            ->setConstructorArgs([$table, $this->settings])
            ->setMethods($methods)
            ->getMock();
        $behavior->expects($this->any())
            ->method('config')
            ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataOk);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject($this->dataOk), $data);
    }

    public function testBeforeMarshalError()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')
            ->disableOriginalConstructor()
            ->setMethods(['isEmptyAllowed'])
            ->getMock();
        $validator->expects($this->once())
            ->method('isEmptyAllowed')
            ->will($this->returnValue(true));

        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->disableOriginalConstructor()
            ->setMethods(['validator'])
            ->getMock();
        $table->expects($this->once())
            ->method('validator')
            ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Dala00\Upload\Model\Behavior\UploadBehavior')
            ->setConstructorArgs([$table, $this->settings])
            ->setMethods($methods)
            ->getMock();
        $behavior->expects($this->any())
            ->method('config')
            ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject, $data);
    }

    public function testBeforeMarshalEmptyAllowed()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')
            ->disableOriginalConstructor()
            ->setMethods(['isEmptyAllowed'])
            ->getMock();
        $validator->expects($this->once())
            ->method('isEmptyAllowed')
            ->will($this->returnValue(false));

        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->disableOriginalConstructor()
            ->setMethods(['validator'])
            ->getMock();
        $table->expects($this->once())
            ->method('validator')
            ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Dala00\Upload\Model\Behavior\UploadBehavior')
            ->setConstructorArgs([$table, $this->settings])
            ->setMethods($methods)
            ->getMock();
        $behavior->expects($this->any())
            ->method('config')
            ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject($this->dataError), $data);
    }

    public function testSave()
    {
        $event = new Event('testevent');
        $options = new ArrayObject;
        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();
        $defaultValue = [
            'name' => 'filename',
            'tmp_name' => 'tmp_name',
            'size' => 5,
            'error' => UPLOAD_ERR_OK,
        ];
        $entity->expects($this->any())
            ->method('get')
            ->will($this->onConsecutiveCalls(
                $defaultValue,
                $defaultValue,
                $defaultValue,
                5
            ));
        $fileSystem = $this->Upload->fileSystem();
        $fileSystem->addFiles($defaultValue['tmp_name']);
        $files = $fileSystem->getFiles();
        $this->Upload->beforeSave($event, $entity, $options);
        $this->Upload->afterSave($event, $entity, $options);
        $newfiles = $fileSystem->getFiles();
        $this->assertEquals(count($files) + 1, count($newfiles));
        $this->assertEquals(true, in_array($defaultValue['tmp_name'], $newfiles));
    }

    public function testSaveForConfirm()
    {
        $event = new Event('testevent');
        $options = new ArrayObject;
        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();
        $defaultValue = [
            'name' => 'filename',
            'tmp_name' => 'tmp_name',
            'size' => 5,
            'error' => UPLOAD_ERR_OK,
        ];
        $cachedValue = $defaultValue;
        $cachedValue['cache'] = 'aiueawefijoa';
        $entity->expects($this->any())
            ->method('get')
            ->will($this->onConsecutiveCalls(
                $defaultValue,
                $defaultValue,
                $defaultValue,
                $defaultValue,
                $defaultValue,
                5
            ));
        $fileSystem = $this->Upload->fileSystem();
        $fileSystem->addFiles($defaultValue['tmp_name']);
        $files = $fileSystem->getFiles();
        $this->Upload->uploadTmpFile($entity);
        $this->Upload->beforeSave($event, $entity, $options);
        $this->Upload->afterSave($event, $entity, $options);
        $newfiles = $fileSystem->getFiles();
        $this->assertEquals(count($files) + 2, count($newfiles));
        $this->assertEquals(false, in_array($cachedValue['cache'], $newfiles));
    }

    public function testUploadTmpFile()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();
        $tmpName = 'tmpname';
        $entity->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                'name' => 'filename',
                'tmp_name' => $tmpName,
                'size' => 5,
                'error' => UPLOAD_ERR_OK,
            ]));

        $fileSystem = $this->Upload->fileSystem();
        $fileSystem->addFiles($tmpName);
        $oldfiles = $fileSystem->getFiles();
        $this->Upload->uploadTmpFile($entity);
        $this->assertEquals(count($oldfiles) + 1, count($fileSystem->getFiles()));
    }

    public function testGetUploadFolder()
    {
        $id = '5';

        $this->Table->expects($this->once())
            ->method('alias')
            ->will($this->returnValue('tablename'));
        $this->Table->expects($this->once())
            ->method('primaryKey')
            ->will($this->returnValue('id'));

        $entity = $this->getMockBuilder('Cake\ORM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $entity->expects($this->once())
            ->method('get')
            ->will($this->returnValue($id));

        $folder = $this->Upload->getUploadFolder($entity, 'test');
        $this->assertContains($id, $folder);
    }
}
