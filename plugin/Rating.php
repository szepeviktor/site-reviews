<?php

namespace GeminiLabs\SiteReviews;

use GeminiLabs\SiteReviews\Database\Query;
use GeminiLabs\SiteReviews\Database\ReviewManager;
use GeminiLabs\SiteReviews\Helpers\Arr;

/**
 * @property string $avatar;
 * @property string $email
 * @property int $ID
 * @property string $ip_address
 * @property bool $is_approved
 * @property bool $is_pinned
 * @property string $name
 * @property array $post_ids
 * @property int $rating
 * @property int $review_id
 * @property array $term_ids
 * @property string $type
 * @property string $url
 * @property array $user_ids
 */
class Rating extends Arguments
{
    /**
     * @var bool
     */
    protected $hasPostsPivot;

    /**
     * @var bool
     */
    protected $hasTermsPivot;

    /**
     * @var bool
     */
    protected $hasUsersPivot;

    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->hasPostsPivot = is_array($this->posts_id);
        $this->hasTermsPivot = is_array($this->terms_id);
        $this->hasUsersPivot = is_array($this->users_id);
        $this->normalize();
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ('post_ids' === $key) {
            return $this->postIds();
        }
        if ('term_ids' === $key) {
            return $this->termIds();
        }
        if ('user_ids' === $key) {
            return $this->userIds();
        }
        return parent::offsetGet($key);
    }

    /**
     * @param mixed $key
     * @return void
     */
    public function offsetSet($key, $value)
    {
        // This class is read-only
    }

    /**
     * @return Review
     */
    public function review()
    {
        return glsr(ReviewManager::class)->single(get_post($this->review_id));
    }

    /**
     * @return array
     */
    protected function normalize()
    {
        $this->set('ID', Helper::castToInt($this->ID));
        $this->set('is_approved', Helper::castToBool($this->is_approved));
        $this->set('is_pinned', Helper::castToBool($this->is_pinned));
        $this->set('post_ids', Helper::castToArray($this->posts_id));
        $this->set('rating', Helper::castToInt($this->rating));
        $this->set('review_id', Helper::castToInt($this->review_id));
        $this->set('term_ids', Helper::castToArray($this->terms_id));
        $this->set('user_ids', Helper::castToArray($this->users_id));
    }

    /**
     * @return array
     */
    protected function postIds()
    {
        if (!$this->hasPostsPivot) {
            $pivot = glsr(Query::class)->ratingPivot('post_id', 'assigned_posts', $this->ID);
            $this->set('post_ids', Arr::uniqueInt($pivot));
            $this->hasPostsPivot = true;
        }
        return $this->get('post_ids');
    }

    /**
     * @return array
     */
    protected function termIds()
    {
        if (!$this->hasTermsPivot) {
            $pivot = glsr(Query::class)->ratingPivot('term_id', 'assigned_terms', $this->ID);
            $this->set('term_ids', Arr::uniqueInt($pivot));
            $this->hasTermsPivot = true;
        }
        return $this->get('term_ids');
    }

    /**
     * @return array
     */
    protected function userIds()
    {
        if (!$this->hasUsersPivot) {
            $pivot = glsr(Query::class)->ratingPivot('user_id', 'assigned_users', $this->ID);
            $this->set('user_ids', Arr::uniqueInt($pivot));
            $this->hasUsersPivot = true;
        }
        return $this->get('user_ids');
    }
}
