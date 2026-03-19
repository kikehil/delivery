import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'images.unsplash.com',
      },
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '8000',
        pathname: '/storage/**',
      },
      {
        protocol: 'https',
        hostname: 'delivery.noddal.cloud',
        pathname: '/storage/**',
      },
      {
        protocol: 'http',
        hostname: 'localhost',
        pathname: '/delivery/backend/public/storage/**',
      },
    ],
  },
};

export default nextConfig;
