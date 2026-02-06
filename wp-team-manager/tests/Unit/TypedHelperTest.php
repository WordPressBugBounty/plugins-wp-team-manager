<?php
declare(strict_types=1);

namespace DWL\Wtm\Tests\Unit;

use PHPUnit\Framework\TestCase;
use DWL\Wtm\Classes\TypedHelper;
use DWL\Wtm\Classes\TeamSettings;

/**
 * Unit tests for TypedHelper class
 */
class TypedHelperTest extends TestCase {
    
    public function testValidateTeamMemberWithValidData(): void {
        $data = [
            'post_title' => 'John Doe',
            'tm_jtitle' => 'Developer'
        ];
        
        $result = TypedHelper::validateTeamMember($data);
        $this->assertTrue($result);
    }
    
    public function testValidateTeamMemberWithMissingTitle(): void {
        $data = [
            'tm_jtitle' => 'Developer'
        ];
        
        $result = TypedHelper::validateTeamMember($data);
        $this->assertIsString($result);
        $this->assertStringContains('post_title', $result);
    }
    
    public function testProcessTeamSettings(): void {
        $settings = [
            'layout_type' => 'grid',
            'columns' => 4,
            'show_image' => 'yes',
            'image_size' => 'large'
        ];
        
        $result = TypedHelper::processTeamSettings($settings);
        
        $this->assertInstanceOf(TeamSettings::class, $result);
        $this->assertEquals('grid', $result->layout);
        $this->assertEquals(4, $result->columns);
        $this->assertTrue($result->showImage);
        $this->assertEquals('large', $result->imageSize);
    }
}