# HueControl
Personal project to program the Philips Hue bridge more easily.

I got fed up on doing all of the configuration manually with a phone without a way to do it on a computer
or to write the same rules for multiple sensors at once.

Programs are hardcoded and features are missing as this is not meant for public use, it only has what I use.
To create more programs, add them to the Program -namespace and to the ProgramSensor RequestHandler.

## Usage
First create a username with `http://<bridge-ip>/debug/clip.html`
1. Press the link button in the bridge
2. POST `{"devicetype": "something"}` to /api
3. Get the username from the response

`php cli.php <bridge-ip> <username> <command> [...command arguments]`

#### Supported commands
- `GetResourceLinks`
  - List resource links in the Hue Bridge
- `GetLights`
  - List lights in the Hue Bridge
- `GetGroups`
  - List groups (rooms) in the Hue Bridge
- `GetRules`
  - List rules in the Hue Bridge
- `GetScenes [room name]`
  - List all scenes in the Hue Bridge for all rooms or in `room name`
- `DeleteScene <scene id>`
  - Delete a scene with id `scene id`
- `GetSensors`
  - List sensors (buttons, motion sensors, flags, etc.) int he Hue Bridge
- `DeleteUnusedMemorySensors`
  - Delete memory sensors (boolean and integer flags) that are not used by any rules
- `ProgramSensor <sensor name> <group name> <program name>`
  - Apply `program name` to `sensor name` to control room `group name`