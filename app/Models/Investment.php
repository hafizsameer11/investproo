<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
  protected $fillable = [
    'user_id',
    'investment_plan_id',
    'amount',
    'start_date',
    'end_date',
    'status',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'amount' => 'decimal:2',
  ];

  // Relationships
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function investmentPlan()
  {
    return $this->belongsTo(InvestmentPlan::class);
  }

  // Helper methods
  public function getDaysRemainingAttribute()
  {
    if (!$this->end_date) return 0;
    return max(0, now()->diffInDays($this->end_date, false));
  }

  public function getProgressPercentageAttribute()
  {
    if (!$this->start_date || !$this->end_date) return 0;
    
    $totalDays = $this->start_date->diffInDays($this->end_date);
    
    // Prevent division by zero
    if ($totalDays <= 0) {
      // If start and end date are the same or invalid, return 100% if past end date, 0% if not
      if (now()->isAfter($this->end_date)) {
        return 100;
      }
      return 0;
    }
    
    $elapsedDays = $this->start_date->diffInDays(now());
    
    return min(100, max(0, ($elapsedDays / $totalDays) * 100));
  }

  public function getTotalProfitAttribute()
  {
    if (!$this->investmentPlan || !$this->start_date) return 0;
    
    $dailyProfitRate = $this->investmentPlan->profit_percentage / 100;
    $elapsedDays = max(0, $this->start_date->diffInDays(now()));
    
    return $this->amount * $dailyProfitRate * $elapsedDays;
  }
}
