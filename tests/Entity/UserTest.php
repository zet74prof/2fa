<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetAge()
    {
        $user = new User();
        $this->assertNull($user->getAge());

        $user->setBirthDate((new \DateTime())->modify('-20 years'));
        $this->assertEquals(20, $user->getAge());

        // Edge case: birthday is tomorrow (so not yet 21)
        $user->setBirthDate((new \DateTime())->modify('-21 years')->modify('+1 day'));
        $this->assertEquals(20, $user->getAge());
    }

    public function testGetContractCategory()
    {
        $user = new User();
        $this->assertNull($user->getContractCategory());

        // Category 1: < 1 year
        $user->setContractStartDate((new \DateTime())->modify('-6 months'));
        $this->assertEquals(1, $user->getContractCategory());

        // Category 2: 1 <= x < 5
        $user->setContractStartDate((new \DateTime())->modify('-1 year'));
        $this->assertEquals(2, $user->getContractCategory());

        $user->setContractStartDate((new \DateTime())->modify('-4 years'));
        $this->assertEquals(2, $user->getContractCategory());

        // Category 3: 5 <= x < 15
        $user->setContractStartDate((new \DateTime())->modify('-5 years'));
        $this->assertEquals(3, $user->getContractCategory());

        $user->setContractStartDate((new \DateTime())->modify('-14 years'));
        $this->assertEquals(3, $user->getContractCategory());

        // Category 4: >= 15
        $user->setContractStartDate((new \DateTime())->modify('-15 years'));
        $this->assertEquals(4, $user->getContractCategory());

        $user->setContractStartDate((new \DateTime())->modify('-20 years'));
        $this->assertEquals(4, $user->getContractCategory());
    }
}
