import { TextEncoder, TextDecoder } from 'util';

module.exports = async () => {
    // This is required for LoginSession-test.js so that times are rendered in the correct timezone
    process.env.TZ = "UTC";
};
