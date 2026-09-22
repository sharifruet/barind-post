FROM node:20-bookworm-slim
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install --omit=dev
COPY server.js logo.png ./
COPY fonts ./fonts
EXPOSE 3000
CMD ["node", "server.js"]
