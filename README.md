# Workcity Chat (WordPress + WooCommerce)
Author: Thankgod

A role-aware chat system for buyer ↔ merchant/designer/agent conversations with product context.

## Features
- CPT: `wcc_chat_session`
- Real-time feel via AJAX polling
- REST API: `/wp-json/wcc/v1/...`
- Shortcode `[wcc_chat]`
- WooCommerce product context (auto-injects on product page)
- Role-based access (Customer, Shop Manager, Designer, Support Agent)
- Read/unread counters, timestamps
- Dark/Light toggle

## Installation
1. Upload the zip in **Plugins → Add New → Upload Plugin**.
2. Activate **Workcity Chat**.
3. Ensure **WooCommerce** is active.
4. Create **Designer** / **Support Agent** users as needed (roles added on activation).

## Usage
- `[wcc_chat]` anywhere.
- Attributes: `product_id`, `target_role` (`merchant|designer|agent`).

## Demo
See `demo/demo-script.md`.
