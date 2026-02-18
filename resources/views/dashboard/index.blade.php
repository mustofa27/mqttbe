@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 0.5rem;">Welcome, {{ Auth::user()->name }}! 👋</h1>
    <p style="color: #666;">Here's your MQTT management overview</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Projects</h3>
        <div class="value">{{ $projectsCount }}</div>
    </div>
    <div class="stat-card">
        <h3>Devices</h3>
        <div class="value">{{ $devicesCount }}</div>
    </div>
</div>

<div class="card" style="text-align: center; padding: 3rem;">
    <h2>Welcome to ICMQTT</h2>
    <p style="margin: 1rem 0; color: #666;">
        Manage your MQTT projects, devices, and topics from a single dashboard.
    </p>
</div>

<div style="margin-top: 2rem; background: white; padding: 2rem; border-radius: 8px; border-left: 4px solid #007bff;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">IoT Project Setup Guide</h2>
    <ol style="font-size: 1.1rem; line-height: 1.7;">
        <li><strong>Register/Login:</strong> Create an account or log in to your dashboard.</li>
        <li><strong>Create a Project:</strong> Go to <b>Projects</b> and click <b>+ New Project</b>. Fill in the project details and save.</li>
        <li><strong>Add Devices:</strong> In <b>Devices</b>, add your IoT devices. Each device gets a unique <b>Device ID</b> and type (sensor, actuator, dashboard).</li>
        <li><strong>Create Topics:</strong> In <b>Topics</b>, create topics for your project. Each topic is linked to a device and used for MQTT messaging.</li>
        <li><strong>Add Permissions:</strong> For each device (except <code>sys_device</code>), go to <b>Permissions</b> and add a permission to enable MQTT access. Without a permission, the device cannot connect to the broker.</li>
        <li><strong>Configure MQTT Client:</strong> Use the provided MQTT broker details (host, port, username, password) in your IoT device or application. Use the correct <b>Device ID</b> and <b>Topic</b> template.</li>
                <li><strong>Configure MQTT Client:</strong> Use the provided MQTT broker details (host, port, username, password) in your IoT device or application. Use the correct <b>Device ID</b> and <b>Topic</b> template.
                        <details style="margin-top: 1rem;">
                                <summary style="cursor: pointer; font-weight: 500; color: #007bff;">How to Connect to MQTT Broker (Click for details)</summary>
                                <div style="margin-top: 1rem; text-align: left;">
                                        <b>Broker Details:</b><br>
                                        Host: <code>mqtt.icminovasi.my.id</code><br>
                                        Port: <code>8883</code> (SSL/TLS enabled)<br>
                                            Username: <i>Your Project Key</i> (<code>project_key</code>)<br>
                                            Password: <i>Your Project Secret</i> (<code>project_secret</code>)<br>
                                        <br>
                                        <b>Security:</b><br>
                                        - Connection uses SSL/TLS (port 8883).<br>
                                        - Certificates are trusted by default.<br>
                                        - Most clients do <b>not</b> need a custom CA file.<br>
                                        <br>
                                        <b>Example: Python (paho-mqtt)</b><br>
                                        <pre style="background: #f8f9fa; padding: 1em; border-radius: 6px; font-size: 0.95em;">import paho.mqtt.client as mqtt

client = mqtt.Client()
    client.username_pw_set('PROJECT_KEY', 'PROJECT_SECRET')
client.tls_set() # Use default CA certificates
client.connect('mqtt.icminovasi.my.id', 8883)
client.loop_start()</pre>
                                        <b>Example: Node.js (mqtt.js)</b><br>
                                        <pre style="background: #f8f9fa; padding: 1em; border-radius: 6px; font-size: 0.95em;">const mqtt = require('mqtt')
const client = mqtt.connect('mqtts://mqtt.icminovasi.my.id:8883', {
        username: 'PROJECT_KEY',
        password: 'PROJECT_SECRET'
})
client.on('connect', () => {
    console.log('Connected!')
})</pre>
                                        <b>Example: PHP (php-mqtt/client)</b><br>
                                        <pre style="background: #f8f9fa; padding: 1em; border-radius: 6px; font-size: 0.95em;">use PhpMqtt\Client\MqttClient;
$client = new MqttClient('mqtt.icminovasi.my.id', 8883, 'YOUR_CLIENT_ID');
$client->connect('PROJECT_KEY', 'PROJECT_SECRET', true); // true = use TLS</pre> 
        <br>
        <b>Where to find credentials?</b><br>
            - Project Key and Project Secret are shown in the <b>Projects</b> menu.<br>
            - Topic names are shown in the <b>Topics</b> menu.<br>
        <br>
        <b>Troubleshooting:</b>
        <ul style="margin-left: 1.5em;">
            <li>Use port <b>8883</b> (not 1883).</li>
            <li>Check your device credentials and topic names.</li>
            <li>If you get SSL errors, update your device’s CA certificates or use a modern OS.</li>
            <li>Contact support if you have persistent connection issues.</li>
        </ul>
    </details>
                </li>
        <li><strong>Send/Receive Data:</strong> Publish or subscribe to topics using your device. Data will be processed and stored by the system.</li>
        <li><strong>Monitor & Manage:</strong> Use the dashboard to monitor device status, manage topics, and view analytics.</li>
        <li><strong>Subscription & Payments:</strong> Upgrade your plan as needed for more devices, topics, or advanced features.</li>
        <li><strong>Support:</strong> For help, contact support or refer to this guide anytime from the dashboard.</li>
    </ol>
</div>
@endsection
