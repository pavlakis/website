<?php

namespace App\Service;

use App\Model\Event\Entity\Speaker;
use App\Model\Event\Entity\Talk;
use App\Model\Event\Event;
use App\Model\Event\EventManager;
use App\Model\MeetupEvent;


class EventsService
{
    /**
     * @var \GuzzleHttp\Client()
     */
    protected $httpClient;

    /**
     * @var MeetupService
     */
    protected $meetupService;

    /**
     * @var JoindinService
     */
    protected $joindinEventService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var EventManager
     */
    protected $eventManager;


    public function __construct(MeetupService $meetupService, JoindinService $joindinEventService, EventManager $eventManager)
    {
        $this->meetupService            = $meetupService;
        $this->joindinEventService      = $joindinEventService;
        $this->eventManager             = $eventManager;
    }

    /**
     * @return MeetupEvent
     */
    public function getMeetupEvent()
    {
        return $this->meetupService->getMeetupEvent();
    }

    /**
     * @return array
     */
    public function getLatestEvent()
    {
        return $this->meetupService->getLatestEvent();
    }

    public function getEventById($eventID)
    {
        return $this->meetupService->getEventById($eventID);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->meetupService->getAll();
    }

    /**
     * @param $meetupEvents
     * @param $speakers
     * @param $venues
     */
    public function mergeEvents(&$meetupEvents, $speakers, $venues)
    {
        // key it on meetup ID
        $localEvents = array_reduce($this->eventsRepository->getAll(), function($carry, $item) {
            $carry[$item->meetup_id] = $item;
            return $carry;
        });

        if (empty($localEvents)) {
            return;
        }

        // Use only events which exist on the DB
        $meetupEvents = array_intersect_key($meetupEvents, $localEvents);
        foreach ($localEvents as $event) {
            if (array_key_exists($event->meetup_id, $meetupEvents)) {

                // check for speaker
                if (array_key_exists($event->speaker_id, $speakers)) {
                    /** @var Speaker $speaker */
                    $speaker = $speakers[$event->speaker_id];
                    $meetupEvents[$event->meetup_id]['speaker'] = $speaker->getFirstName() . ' '
                                    . $speaker->getLastName() . ' (' . $speaker->getTwitter() . ')';
                } else {
                    $meetupEvents[$event->meetup_id]['speaker'] = '-';
                }

                $meetupEvents[$event->meetup_id]['joindin_url'] = $event->joindin_url ?? '-';

            }
        }
    }

    /**
     * @return array
     */
    public function getVenues()
    {
       return $this->meetupService->getVenues();
    }

    /**
     * @param $venueID
     * @return \App\Model\Event\Entity\Venue
     */
    public function getVenueById($venueID)
    {
        return $this->meetupService->getVenueById($venueID);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function createEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Save event references to the DB
     *
     * @param  string $eventName If null, use it through the event object
     * @return \App\Model\Event\Entity\Event
     */
    public function updateEvents($eventName = null)
    {
        $eventName = $eventName ?? $this->event->getName();

        $eventEntity = new \App\Model\Event\Entity\Event(
            $this->meetupService->getMeetupEvent()->getMeetupEventID(),
            $this->event->getVenue()->getId(),
            $eventName,
            $this->joindinEventService->getJoindinEvent()->getTalkID(),
            $this->joindinEventService->getJoindinEvent()->getTalkUrl(),
            $this->event->getTalk()->getSpeaker()->getId(),
            $this->event->getSupporter()->getId()
        );

        $this->eventManager->saveEvent($eventEntity);

        return $eventEntity;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createMeetup()
    {
        return $this->meetupService->createMeetup();
    }

    /**
     * @param $userID
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function createJoindinEvent($userID)
    {
        if ($this->eventManager->eventExists($this->event->getName())) {
            throw new \Exception('An event by the name: ' . $this->event->getName() . ', already exists.');
        }

        return $this->joindinEventService->createEvent($userID);
    }

    /**
     * @param int $userID
     * @param string $language
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createJoindinTalk($userID, $language = 'English - UK')
    {
        return $this->joindinEventService->createTalk($this->event, $userID, $language);
    }

    /**
     * @param $meetupID
     * @return array
     */
    public function getEventInfo($meetupID) : array
    {
        return $this->eventsRepository->getByMeetupID($meetupID)[0] ?: [];
    }

    public function isEventApproved($meetupID = null)
    {
        //return $this->joindinService->isEventApproved($meetupID);
    }
}