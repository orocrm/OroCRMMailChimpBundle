<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

class ConnectionFormHandler extends ApiFormHandler
{
    /** @var StaticSegment */
    protected $oldSegment;

    /**
     * @param StaticSegment $entity
     * @return bool
     */
    public function process($entity)
    {
        if ($entity->getId()) {
            $this->oldSegment = $entity;
            $entity = $this->createSegmentCopy($entity);
        }

        return parent::process($entity);
    }

    /**
     * @param StaticSegment $entity
     */
    protected function onSuccess($entity)
    {
        if ($this->oldSegment) {
            if ($this->oldSegment->getSubscribersList() !== $entity->getSubscribersList() &&
                !$this->campaignExistsForSegment($this->oldSegment)
            ) {
                $this->manager->remove($this->oldSegment);
            } else {
                $this->oldSegment->setMarketingList(null);
            }
        }

        parent::onSuccess($entity);
    }

    /**
     * @param StaticSegment $segment
     *
     * @return bool
     */
    protected function campaignExistsForSegment(StaticSegment $segment)
    {
        return (bool) $this->manager->getRepository('OroCRMMailChimpBundle:Campaign')
            ->findOneByStaticSegment($segment);
    }

    /**
     * @param StaticSegment $segment
     *
     * @return StaticSegment
     */
    protected function createSegmentCopy(StaticSegment $segment)
    {
        return (new StaticSegment())
            ->setChannel($segment->getChannel())
            ->setLastReset($segment->getLastReset())
            ->setMarketingList($segment->getMarketingList())
            ->setName($segment->getName())
            ->setOwner($segment->getOwner())
            ->setRemoteRemove($segment->getRemoteRemove())
            ->setSegmentMembers(new ArrayCollection())
            ->setSubscribersList($segment->getSubscribersList())
            ->setSyncStatus(StaticSegment::STATUS_NOT_SYNCED)
            ->setSyncedExtendedMergeVars(new ArrayCollection());
    }
}
