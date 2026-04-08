FROM nginx:alpine

# Copy custom nginx config (will be placed as default site)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy static site files
COPY . /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
