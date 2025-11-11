/**
 * Presets values specifically for the forumify API.
 * Should not be used as a generic replacement for fetch/xhr.
 *
 * @param {string} url
 */
export const request = (url, options) => {
  const headers = {
    'Accept': 'application/ld+json',
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  const body = options.data ? JSON.stringify(options.data) : undefined;

  return fetch(url, {
    method: options.method || 'GET',
    headers,
    body,
  });
};
