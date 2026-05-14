<?php
namespace Abbott\SmileSearch\Plugin;

class Query
{
    /**
     * AroundSaveIncrementalPopularity
     *
     * @param $subject
     * @param $callable
     * @return mixed
     */
    public function aroundSaveIncrementalPopularity($subject, $callable)
    {
        return $subject;
    }

    /**
     * AroundSaveNumResults
     *
     * @param $subject
     * @param $callable
     * @param $numResults
     * @return mixed
     */
    public function aroundSaveNumResults($subject, $callable, $numResults)
    {
        if ($numResults > 0) {
            $subject->getResource()->saveIncrementalPopularity($subject);
            $subject->setNumResults($numResults);
            $subject->getResource()->saveNumResults($subject);
        }
        return $subject;
    }
}
