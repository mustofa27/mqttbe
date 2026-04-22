@extends('layouts.app')

@section('title', 'Setup Guide')

@section('content')
<div class="container">
    <h1 class="page-title">Setup Guide</h1>
    <p class="page-subtitle">Complete this checklist to onboard your IoT project and connect devices to the broker.</p>

    <div class="guide-card">
        <h2>IoT Project Setup Guide</h2>
        <ol class="guide-list">
            <li><strong>Register/Login:</strong> Create an account or log in to your dashboard.</li>
            <li><strong>Create a Project:</strong> Go to <strong>Projects</strong> and click <strong>+ New Project</strong>. Fill in the project details and save.</li>
            <li><strong>Add Devices:</strong> In <strong>Devices</strong>, add your IoT devices. Each device gets a unique <strong>Device ID</strong> and type (sensor, actuator, dashboard).</li>
            <li><strong>Create Topics:</strong> In <strong>Topics</strong>, create topics for your project. Each topic is linked to a device and used for MQTT messaging.</li>
            <li><strong>Add Permissions:</strong> For each device (except <code>sys_device</code>), go to <strong>Permissions</strong> and add permission to enable MQTT access.</li>
            <li>
                <strong>Configure MQTT Client:</strong> Use the MQTT broker details in your device/application with the correct <strong>Device ID</strong> and topic template.
                <details class="guide-details">
                    <summary>How to connect to MQTT broker</summary>
                    <div class="details-content">
                        <p><strong>Broker Details</strong></p>
                        <p>
                            Host: <code>mqtt.icminovasi.my.id</code><br>
                            Port: <code>8883</code> (SSL/TLS)<br>
                            Username: <em>Your Project Key</em> (<code>project_key</code>)<br>
                            Password: <em>Your Project Secret</em> (<code>project_secret</code>)
                        </p>
                        <p><strong>Security</strong></p>
                        <ul>
                            <li>Connection uses SSL/TLS on port <strong>8883</strong>.</li>
                            <li>Certificates are generally trusted by default.</li>
                            <li>Most clients do not require a custom CA file.</li>
                        </ul>
                        <p><strong>Example: Python (paho-mqtt)</strong></p>
                        <pre>import paho.mqtt.client as mqtt

client = mqtt.Client()
client.username_pw_set('PROJECT_KEY', 'PROJECT_SECRET')
client.tls_set()
client.connect('mqtt.icminovasi.my.id', 8883)
client.loop_start()</pre>
                        <p><strong>Example: Node.js (mqtt.js)</strong></p>
                        <pre>const mqtt = require('mqtt')

const client = mqtt.connect('mqtts://mqtt.icminovasi.my.id:8883', {
  username: 'PROJECT_KEY',
  password: 'PROJECT_SECRET'
})

client.on('connect', () => {
  console.log('Connected!')
})</pre>
                        <p><strong>Example: PHP (php-mqtt/client)</strong></p>
                        <pre>use PhpMqtt\Client\MqttClient;

$client = new MqttClient('mqtt.icminovasi.my.id', 8883, 'YOUR_CLIENT_ID');
$client->connect('PROJECT_KEY', 'PROJECT_SECRET', true);</pre>
                        <p><strong>Where to find credentials?</strong></p>
                        <ul>
                            <li>Project Key and Project Secret are in <strong>Projects</strong>.</li>
                            <li>Topic names are in <strong>Topics</strong>.</li>
                        </ul>
                        <p><strong>Troubleshooting</strong></p>
                        <ul>
                            <li>Use port <strong>8883</strong> (not 1883).</li>
                            <li>Check device credentials and topic names.</li>
                            <li>If SSL errors occur, update CA certificates on the device.</li>
                            <li>Contact support if connection issues persist.</li>
                        </ul>
                    </div>
                </details>
            </li>
            <li><strong>Send/Receive Data:</strong> Publish or subscribe to topics from your device.</li>
            <li><strong>Monitor & Manage:</strong> Use Usage and Advanced Dashboard to monitor activity.</li>
            <li><strong>Subscription & Payments:</strong> Upgrade your plan for higher limits and more features.</li>
            <li><strong>Support:</strong> Contact support for further help.</li>
        </ol>

        <div class="guide-actions">
            <a href="{{ route('projects.create') }}" class="btn-small">Create Project</a>
            <a href="{{ route('devices.create') }}" class="btn-small">Add Device</a>
            <a href="{{ route('topics.create') }}" class="btn-small">Create Topic</a>
            <a href="{{ route('permissions.create') }}" class="btn-small">Set Permissions</a>
        </div>
    </div>
</div>

<style>
    .page-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    .guide-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #2563eb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .guide-card h2 {
        margin: 0 0 0.5rem;
        font-size: 1.1rem;
        color: #1e3a8a;
    }

    .guide-card p {
        margin: 0 0 0.9rem;
        color: #334155;
    }

    .guide-list {
        margin: 0;
        padding-left: 1.25rem;
        line-height: 1.7;
        color: #1f2937;
    }

    .guide-list li {
        margin-bottom: 0.75rem;
    }

    .guide-details {
        margin-top: 0.75rem;
    }

    .guide-details summary {
        cursor: pointer;
        color: #2563eb;
        font-weight: 600;
    }

    .details-content {
        margin-top: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.9rem;
    }

    .details-content p {
        margin: 0.5rem 0;
    }

    .details-content ul {
        margin: 0.4rem 0 0.8rem 1.1rem;
    }

    .details-content pre {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        overflow-x: auto;
        font-size: 0.85rem;
        margin: 0.6rem 0 0.9rem;
    }

    .guide-actions {
        margin-top: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .btn-small {
        display: inline-block;
        padding: 0.45rem 0.8rem;
        background: #4f46e5;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .btn-small:hover {
        background: #4338ca;
    }
</style>
@endsection
