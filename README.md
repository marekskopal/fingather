# FinGather — Your Ultimate Portfolio Tracker

<p align="center">
  <img src="https://www.fingather.com/app/images/fingather.svg" alt="FinGather" width="400" />
</p>

<p align="center">
  <strong>Take control of your financial future with comprehensive investment tracking</strong>
</p>

<p align="center">
  <a href="https://www.fingather.com">Website</a> •
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#contributing">Contributing</a>
</p>

---

## Overview

Tracking all of your investments at once is challenging — most brokers lack the long-term analytical insights investors need. **FinGather** solves this by providing a unified portfolio tracking platform that gives you a detailed view of your entire investment landscape.

Whether you're managing stocks, cryptocurrencies, ETFs, bonds, or mutual funds, FinGather consolidates everything in one place with powerful analytics and beautiful visualizations.

This repository contains the source code for the FinGather community edition.

## Features

### Effortless Integration
Seamlessly connect with many broker, crypto, and investment platforms. Import your assets via CSV/Excel uploads or connect directly through supported APIs for real-time data access.

| Platform | CSV | Excel | API |
|---|:---:|:---:|:---:|
| Trading212 | ✅ | | ✅ |
| XTB | | ✅ | |
| Degiro | ✅ | | |
| eToro | | ✅ | ✅ |
| Portu | ✅ | | |
| Interactive Brokers | ✅ | | |
| Anycoin | ✅ | | |
| Binance | ✅ | | |
| Coinbase | ✅ | | |
| Revolut | ✅ | | |
| Fio Banka | ✅ | | |
| Patria Finance | | ✅ | |

### Advanced Analytics
Gain in-depth insights into:
- Portfolio performance and returns
- Dividend income tracking
- Fees and taxes overview
- Unrealized and realized gains/losses
- Currency risk assessment (FX Impact)

### Global Market Coverage
Access detailed reports, tables, and visualizations of assets from **80+ global exchanges** — all in one dashboard.

### Custom Asset Grouping
Categorize your investments the way you want:
- Create custom groups tailored to your strategy
- Use preset categories based on countries, asset types, or industry sectors
- Organize by any criteria that matters to you

### Benchmark Comparisons
Compare your portfolio performance against any benchmark you choose:
- S&P 500
- Bitcoin
- Any other asset in our database

Tailor your performance analysis to match your investment goals.

### Portfolio History
Track your portfolio value over time with interactive charts. See how your investments have grown and analyze performance across different time periods.

### Multi-Portfolio Support
Manage multiple portfolios with different currencies and strategies — all from a single account.

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.5+ with Spiral RoadRunner |
| Frontend | Angular 21 (Standalone Components) |
| Database | MariaDB |
| Caching | Memcached & Redis |
| Queue | RabbitMQ |
| Container | Docker |

## Prerequisites

FinGather uses [TwelveData](https://twelvedata.com) APIs to load stock and crypto data. You'll need an API key to run the application.

1. Register at [https://twelvedata.com/register](https://twelvedata.com/register)
2. Get your free API key

## Installation

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone https://github.com/marekskopal/fingather.git
   cd fingather
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```

   Edit `.env` and set:
   - `TWELVEDATA_API_KEY` — Your TwelveData API key (required)
   - `PROXY_SSL_CERT` — Path to SSL certificate (optional, for HTTPS)
   - `PROXY_SSL_KEY` — Path to SSL private key (optional, for HTTPS)

3. **Build and run**
   ```bash
   docker compose up -d --build
   ```

4. **Access the application**

   Open [http://localhost](http://localhost) (or your configured domain) in your browser.

### Development Setup

For development with admin tools (Adminer for database, Buggregator for debugging):

```bash
docker compose --profile dev up -d
```

## Project Structure

```
fingather/
├── backend/           # PHP backend application
│   ├── src/
│   │   ├── Controller/    # HTTP endpoints
│   │   ├── Dto/           # Data Transfer Objects
│   │   ├── Model/         # Entities & Repositories
│   │   └── Service/       # Business logic
│   └── migrations/        # Database migrations
├── frontend/          # Angular frontend application
│   ├── src/
│   │   ├── app/           # Application modules
│   │   └── i18n/          # Translations (EN, CS)
│   └── ...
└── docker-compose.yml
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Links

- **Website**: [https://www.fingather.com](https://www.fingather.com)
- **Issues**: [GitHub Issues](https://github.com/marekskopal/fingather/issues)

---

<p align="center">
  Made with ❤️ for investors who want clarity in their portfolio
</p>
