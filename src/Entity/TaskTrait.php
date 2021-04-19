<?php

declare(strict_types=1);


namespace App\Entity;


use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

trait TaskTrait
{
    /**
     * @Groups({"detail"})
     */
    public function getTasks()
    {
        return $this->tasks->filter(function(Task $task) {
            return $task->getTaskStatus() === 'open';
        });
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getAllTasks()
    {
        return $this->tasks;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getNextTask()
    {
        $test = 1;
        $iterator = $this->tasks->filter(function(Task $task) {
            return $task->getTaskStatus() === 'open' && $task->getStartDateAsDateTime() < new DateTimeImmutable();
        })->getIterator();
        $iterator->uasort(function (Task $a, Task $b) {
            return ( is_null($a->getDueDate()) OR $a->getDueDate() < $b->getDueDate()) ? 1 : -1;
        });
        return (new ArrayCollection(iterator_to_array($iterator)))->first();
    }
}
