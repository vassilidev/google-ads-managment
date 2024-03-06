# Documentation

## ULEAD ADS Module

### Authorization

**Basic Auth**
- **Username**: `<username>`
- **Password**: `<password>`

### GET All Campaigns

Get all campaigns from the server.

**Endpoint**: `https://unitead.the-forge.agency/api/ads`

### GET Campaign From Ad ID

Get a specific campaign using its Ad ID.

**Endpoint**: `http://localhost/api/ads/20023442762`

### POST Fetch Campaign Run Script

Fetch campaign run script from the server.

**Endpoint**: `http://localhost/api/fetch-campaigns`

### GET PING PONG Status

Check the status of the server.

**Endpoint**: `http://localhost/api/ping`

### PUT Update Name / Budget / CPA Target from Ad ID

Update the name, budget, and CPA target of a campaign using its Ad ID.

**Endpoint**: `http://localhost/api/ads/20023442762`

**Body**:
```json
{
    "name": "",
    "budget": "",
    "cpa_target": ""
}
