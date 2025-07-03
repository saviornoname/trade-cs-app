# Database Schema

This document describes the current database schema used by the trading bot.
It includes new tables and columns introduced for extended logging and
notification features.

## Tables

### items
Stores a skin reference used across the project.

### dmarket_targets
Represents user-defined targets for DMarket purchases.

### user_active_targets
Tracks targets that are currently active for a user.

### user_watchlist_items
Stores items added to a user's watchlist.

### watchlist_item_filters
A normalized table with filters for each watchlist item such as
float range, seed and phase.

### buff_offers
Stores offers scraped from Buff marketplace.

### deals
Records profitable deals detected between marketplaces.

### d_market_histories
Extended history of DMarket prices. It now contains item reference and
snapshot timestamp for precise logging.

### price_snapshots
Periodic snapshot of prices for a particular item on a marketplace.

### market_diffs
Computed difference between two marketplace prices.

### notification_logs
Stores notifications sent to users about new deals or actions.

## Indexes
* `deals.item_id`
* `user_active_targets (user_id, is_active)`
* `watchlist_item_filters (paint_seed, phase)`
* `d_market_histories (item_id, snapshot_at)`
* `price_snapshots (item_id, marketplace, snapshot_at)`
