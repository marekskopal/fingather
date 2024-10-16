# FinGather — Your Ultimate Portfolio Tracker

<img src="https://www.fingather.com/app/images/fingather.svg" alt="FinGather" width="400" />

Manage and optimize your investments with [FinGather](https://www.fingather.com), a portfolio tracking platform that lets you take control of your financial future. Whether you're managing stocks, cryptocurrencies, ETFs, bonds, or mutual funds, FinGather give you a detailed view of your entire investment landscape

This repository contains the source code for the FinGather community edition web application.
The application is built using PHP on backend and Angular on frontend and uses MariaDB as relation database and memcached and Redis for cache layers.

## Prerequisites
FinGather currently uses [TwelveData](https://twelvedata.com) APIs to load stock and crypto data. You can get API key on [https://twelvedata.com/register](https://twelvedata.com/register)

## Install

1) Clone this repo

2) Create `.env` file from `.env.example` and fill variable `TWELVEDATA_API_KEY`. If you want to use SSL fill
   `PROXY_SSL_CERT` and `PROXY_SSL_KEY` variables with absolute path to your SSL cert and key. 

3) Build and run
```bash
    docker compose up -d --build
```

