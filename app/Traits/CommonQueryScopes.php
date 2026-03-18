<?php

namespace App\Traits;

trait CommonQueryScopes
{
    public function scopeFilterByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeSearchByTitle($query, $search)
    {
        return $query->where('title', 'LIKE', '%' . $search . '%');
    }

    public function scopeFilterByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFilterByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeOrderByLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeWithRelations($query, array $relations = [])
    {
        return $query->with($relations);
    }
}