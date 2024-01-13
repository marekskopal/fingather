# FinGather - stock and crypto portfolio tracker

Currently in development.

## Install

1) First you need API key. FinGather use [AlphaVantage](https://www.alphavantage.co) APIs to load stock and crypto data.
You can get API key on [https://www.alphavantage.co/support/#api-key](https://www.alphavantage.co/support/#api-key)

2) Clone this repo

3) Create `.env` file from `.env.example` and fill variable `ALPHAVANTAGE_API_KEY`. If you want to use SSL fill
   `PROXY_SSL_CERT` and `PROXY_SSL_KEY` variables with absolute path to your SSL cert and key. 

4) Build and run `
    docker compose up -d --build
`

