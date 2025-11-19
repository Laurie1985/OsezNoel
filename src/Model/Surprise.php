<?php
namespace App\Model;

use App\Config\MongoDB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;

class Surprise
{
    private Collection $collection;

    public function __construct()
    {
        // Récupère la collection 'surprises' depuis MongoDB
        $this->collection = MongoDB::getCollection('surprises');
    }

    /**
     * Récupérer toutes les surprises d'un calendrier
     */
    public function findByCalendarId(string $calendarId): array
    {
        $cursor = $this->collection->find(['calendar_id' => $calendarId]);

        // toArray() convertit le curseur MongoDB en tableau PHP
        return $cursor->toArray();
    }

    /**
     * Récupérer la surprise d'un jour spécifique
     */
    public function findByCalendarIdAndDay(string $calendarId, int $day): ?array
    {
        $surprise = $this->collection->findOne([
            'calendar_id' => $calendarId,
            'day'         => $day,
        ]);

        return $surprise ? $surprise->getArrayCopy() : null;
    }

    /**
     * Créer une nouvelle surprise
     */
    public function create(array $data): string
    {
        // Ajouter des champs automatiques
        $data['is_opened']  = false;
        $data['opened_at']  = null;
        $data['created_at'] = new UTCDateTime(); // Date MongoDB

        $result = $this->collection->insertOne($data);

        return (string) $result->getInsertedId();
    }

    /**
     * Modifier une surprise
     */
    public function update(string $id, array $data): bool
    {
        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );

        return $result->getModifiedCount() > 0;
    }

    /**
     * Supprimer une surprise
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne([
            '_id' => new ObjectId($id),
        ]);

        return $result->getDeletedCount() > 0;
    }

    /**
     * Marquer une surprise comme ouverte
     */
    public function markAsOpened(string $id): bool
    {
        // Met à jour is_opened et opened_at
        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            [
                '$set' => [
                    'is_opened' => true,
                    'opened_at' => new UTCDateTime(), // Date actuelle
                ],
            ]
        );

        return $result->getModifiedCount() > 0;
    }

    /**
     * Compter le nombre de surprises d'un calendrier
     */
    public function countByCalendarId(string $calendarId): int
    {
        // countDocuments() compte les documents qui correspondent au filtre
        return $this->collection->countDocuments([
            'calendar_id' => $calendarId,
        ]);
    }

    /**
     * Vérifier si un calendrier a ses 24 surprises
     */
    public function isCalendarComplete(string $calendarId): bool
    {
        $count = $this->countByCalendarId($calendarId);
        return $count >= 24;
    }

    /**
     * Supprimer toutes les surprises d'un calendrier
     */
    public function deleteByCalendarId(string $calendarId): bool
    {
        $result = $this->collection->deleteMany([
            'calendar_id' => $calendarId,
        ]);

        return $result->getDeletedCount() > 0;
    }
}
