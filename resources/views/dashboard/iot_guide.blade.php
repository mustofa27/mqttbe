@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">IoT Project Setup Guide</h1>
    <ol style="font-size: 1.1rem; line-height: 1.7;">
        <li><strong>Register/Login:</strong> Create an account or log in to your dashboard.</li>
        <li><strong>Create a Project:</strong> Go to <b>Projects</b> and click <b>+ New Project</b>. Fill in the project details and save.</li>
        <li><strong>Add Devices:</strong> In <b>Devices</b>, add your IoT devices. Each device gets a unique <b>Device ID</b> and type (sensor, actuator, dashboard).</li>
        <li><strong>Create Topics:</strong> In <b>Topics</b>, create topics for your project. Each topic is linked to a device and used for MQTT messaging.</li>
        <li><strong>Configure MQTT Client:</strong> Use the provided MQTT broker details (host, port, username, password) in your IoT device or application. Use the correct <b>Device ID</b> and <b>Topic</b> template.</li>
        <li><strong>Send/Receive Data:</strong> Publish or subscribe to topics using your device. Data will be processed and stored by the system.</li>
        <li><strong>Monitor & Manage:</strong> Use the dashboard to monitor device status, manage topics, and view analytics.</li>
        <li><strong>Subscription & Payments:</strong> Upgrade your plan as needed for more devices, topics, or advanced features.</li>
        <li><strong>Support:</strong> For help, contact support or refer to this guide anytime from the dashboard menu.</li>
    </ol>
    <div style="margin-top: 2rem; color: #666; font-size: 1rem;">
        <b>Tip:</b> The default device <code>sys_device</code> is managed by the system and cannot be edited or deleted.
    </div>
</div>
@endsection
