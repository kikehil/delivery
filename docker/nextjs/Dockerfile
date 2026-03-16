FROM node:20-alpine

WORKDIR /app

# Install dependencies only when needed
COPY package*.json ./
RUN npm install

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]
