<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional;

class InheritanceTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testCollectionPerClassInheritance()
    {
        $profile = new \Documents\Profile();
        $profile->setFirstName('Jon');

        $user = new \Documents\SpecialUser();
        $user->setUsername('specialuser');
        $user->setProfile($profile);

        $this->dm->persist($user);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertTrue($user->getId() !== '');
        $this->assertTrue($user->getProfile()->getProfileId() !== '');

        $query = $this->dm->createQuery('Documents\SpecialUser')
            ->field('id')
            ->equals($user->getId());
        $user = $query->getSingleResult();

        $user->getProfile()->setLastName('Wage');
        $this->dm->flush();
        $this->dm->clear();

        $user = $query->getSingleResult();
        $this->assertEquals('Wage', $user->getProfile()->getLastName());
        $this->assertTrue($user instanceof \Documents\SpecialUser);
    }

    public function testSingleCollectionInhertiance()
    {
        $subProject = new \Documents\SubProject('Sub Project');
        $this->dm->persist($subProject);
        $this->dm->flush();

        $coll = $this->dm->getDocumentCollection('Documents\SubProject');
        $document = $coll->findOne(array('name' => 'Sub Project'));
        $this->assertEquals('sub-project', $document['type']);

        $project = new \Documents\OtherSubProject('Other Sub Project');
        $this->dm->persist($project);
        $this->dm->flush();

        $coll = $this->dm->getDocumentCollection('Documents\OtherSubProject');
        $document = $coll->findOne(array('name' => 'Other Sub Project'));
        $this->assertEquals('other-sub-project', $document['type']);

        $this->dm->clear();

        $document = $this->dm->findOne('Documents\SubProject', array('name' => 'Sub Project'));
        $this->assertInstanceOf('Documents\SubProject', $document);

        $document = $this->dm->findOne('Documents\SubProject', array('name' => 'Sub Project'));
        $this->assertInstanceOf('Documents\SubProject', $document);

        $document = $this->dm->findOne('Documents\Project', array('name' => 'Sub Project'));
        $this->assertInstanceOf('Documents\SubProject', $document);
        $this->dm->clear();

        $id = $document->getId();
        $document = $this->dm->find('Documents\Project', $id);
        $this->assertInstanceOf('Documents\SubProject', $document);

        $document = $this->dm->findOne('Documents\Project', array('name' => 'Other Sub Project'));
        $this->assertInstanceOf('Documents\OtherSubProject', $document);

    }

    public function testPrePersistIsCalledFromMappedSuperClass()
    {
        $user = new \Documents\User();
        $user->setUsername('test');
        $this->dm->persist($user);
        $this->dm->flush();
        $this->assertTrue($user->persisted);
    }
}