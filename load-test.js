import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend } from 'k6/metrics';
import { uuidv4 } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';
import { htmlReport } from "https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js";

// Кастомные метрики
let userTime = new Trend('user_response_time');
let roleTime = new Trend('role_response_time');

export let options = {
    stages: [
        { duration: '30s', target: 5 },
        { duration: '1m', target: 10 },
        { duration: '30s', target: 0 },
    ],
};

const BASE_URL = 'http://localhost:8080/api';
const TOKEN = '10|FZpDrAslz1laTHRvIP9ifAy2Qif1ADCkN4BTsTL5c036b233';

export function handleSummary(data) {
    return {
        "load-report.html": htmlReport(data),
    };
}

export default function () {
    // GET /users
    let resUsers = http.get(`${BASE_URL}/users`, {
        headers: { Authorization: `Bearer ${TOKEN}` },
    });
    userTime.add(resUsers.timings.duration);
    check(resUsers, { 'users status 200': (r) => r.status === 200 });

    // POST /users
    let payloadUser = JSON.stringify({
        name: `Test User ${Math.floor(Math.random() * 1000)}`,
        email: `test${uuidv4()}@example.com`,
        password: 'password'
    });
    let resCreateUser = http.post(`${BASE_URL}/users`, payloadUser, {
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${TOKEN}`
        },
    });
    userTime.add(resCreateUser.timings.duration);

    if (resCreateUser.status !== 201) {
        console.error('Create user failed:', resCreateUser.status, resCreateUser.body);
    }
    check(resCreateUser, { 'create user 201': (r) => r.status === 201 });

    // GET /roles
    let resRoles = http.get(`${BASE_URL}/roles`, {
        headers: { Authorization: `Bearer ${TOKEN}` },
    });
    roleTime.add(resRoles.timings.duration);
    check(resRoles, { 'roles status 200': (r) => r.status === 200 });

    // POST /roles
    let payloadRole = JSON.stringify({
        name: `Role ${uuidv4()}`.slice(0, 40)
    });
    let resCreateRole = http.post(`${BASE_URL}/roles`, payloadRole, {
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${TOKEN}`
        },
    });
    roleTime.add(resCreateRole.timings.duration);

    if (resCreateRole.status !== 201) {
        console.error('Create role failed:', resCreateRole.status, resCreateRole.body);
    }
    check(resCreateRole, { 'create role 201': (r) => r.status === 201 });

    sleep(1);
}
