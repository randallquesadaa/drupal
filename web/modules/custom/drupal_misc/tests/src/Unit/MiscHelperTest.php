<?php

declare(strict_types=1);

namespace Drupal\Tests\drupal_misc\Unit;

use Drupal\drupal_misc\Helper\MiscHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\drupal_misc\Helper\MiscHelper
 * @group drupal_misc
 */
class MiscHelperTest extends UnitTestCase {

  /**
   * @covers ::add
   */
  public function testAdd(): void {
    $helper = new MiscHelper();
    $this->assertSame(5, $helper->add(2, 3));
  }

}
