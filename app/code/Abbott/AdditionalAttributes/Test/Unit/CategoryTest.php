<?php
namespace Abbott\AdditionalAttributes\Test\Unit;

class CategoryTest extends \PHPUnit\Framework\TestCase{
    public $block;
    public $inputResult;
    public $inputCategory;
    public $expected1;
    public $expected2;
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\AdditionalAttributes\Plugin\Category::class)->disableOriginalConstructor()->getMock();
        $this->inputResult = array(0=>array('label' => 'Category','count'=>3,'attribute_code'=>'category_id'));
        $this->inputResult[0]['options'][0] = array('label'=>45,'value'=>45,'count'=>8);
        $this->inputResult[0]['options'][1] = array('label'=>'EleCare','value'=>45,'count'=>8);
        $this->inputResult[0]['options'][2] = array('label'=>'EleCare Jr','value'=>45,'count'=>8);

        $this->inputCategory = array('46'=>'EleCare','48'=>'EleCare Jr.');

        $this->expected1 = array(0=>array('label' => 'Category','count'=>3,'attribute_code'=>'category_id'));
        $this->expected1[0]['options'][2] = array('label'=>'EleCare Jr','value'=>45,'count'=>8);
        $this->expected1[0]['options'][1] = array('label'=>'EleCare','value'=>45,'count'=>8);

        $this->expected2 = array(0=>array('label' => 'Category','count'=>3,'attribute_code'=>'category_id'));
        $this->expected2[0]['options'][0] = array('label'=>45,'value'=>45,'count'=>8);
        $this->expected2[0]['options'][2] = array('label'=>'EleCare Jr','value'=>45,'count'=>8);
        $this->expected2[0]['options'][1] = array('label'=>'EleCare','value'=>45,'count'=>8);
    }

    public function testafterBuild(){
        $testMethod = new \ReflectionMethod(\Abbott\AdditionalAttributes\Plugin\Category::class, 'getResult');
        $testMethod->setAccessible(true);
        $resultPositive =  $testMethod->invokeArgs($this->block, [$this->inputResult,$this->inputCategory]);
        $resultNegative =  $testMethod->invokeArgs($this->block, [$this->inputResult,$this->inputCategory]);

        $this->assertEquals($resultPositive, $this->expected1);
        $this->assertNotEquals($resultNegative, $this->expected2);
    }
}
