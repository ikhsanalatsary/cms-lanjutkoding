docker compose -f "docker-compose.yaml" up -d --build
# Expose WP to yuk.lanjutkoding.com
cloudflared tunnel run ikhsan-macbook-vpc-02
# Expose DB to data.lanjutkoding.com
# cloudflared tunnel --config config-db-lanjutkoding.yml run ikhsan-macbook-vpc-03