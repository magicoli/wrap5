# Self-Hosted Casting Video Assembly Platform

## Goal

Create a lightweight collaborative web platform dedicated to casting video assembly workflows.

The platform should:

- centralize incoming candidate videos,
- automate the default assembly workflow,
- allow manual exceptions when needed,
- generate a clean review interface for clients/directors.

The philosophy is:

> "Automation first, manual editing only when necessary."

---

# Existing Workflow

## Current Process

### 1. Receive videos

Sources include:

- casting directors
- agencies
- actors/comedians

Upload methods:

- WeTransfer
- SFTP
- self-hosted upload interface (`jquery-file-upload`)
- possibly direct browser uploads later

---

### 2. Define assemblies

Example:

```text
John Doe.mov = 1.mov + 2.mov + 3.mov
Jane Foo.mov = 4.mov + 5.mov + 7.mov
```

Currently based on:

- filenames
- thumbnails
- email instructions

---

### 3. Automated processing

Current scripts:

- normalize/export to MP4
- trim if necessary
- concatenate videos
- generate final outputs

Tools:

- FFmpeg
- CLI scripts

---

### 4. Delivery

Generated videos are displayed on a web page.

---

# Missing Features

## Priority Features

### Assembly Builder UI

Need a visual interface to:

- create "lists" visually,
- drag/drop clips,
- reorder clips,
- associate clips to an actor/project.

Example:

```text
[Clip 1] [Clip 2] [Clip 3]
          ↓
      John Doe
```

---

### Basic Editing Tools

Only lightweight editing is required.

Needed:

- trim start/end,
- crop if necessary,
- optional mute,
- optional rotation.

No need for:

- transitions,
- effects,
- compositing,
- color grading,
- advanced motion graphics.

---

### Timeline (Optional / Exception Cases)

Timeline editing should exist only for edge cases.

Default workflow remains:

```text
select clips → auto-assemble
```

Timeline should therefore be:

- simple,
- secondary,
- lightweight.

---

### Better Review Front-End

Need a cleaner client-facing interface similar to YouTube/Vimeo review pages.

Desired layout:

```text
+----------------------+
|      Video Player    |
+----------------------+

[ Candidate List ]
- John Doe
- Jane Foo
- ...
```

Desired features:

- fast loading,
- thumbnails,
- keyboard navigation,
- mobile friendly,
- searchable list,
- comments/notes later maybe.

---

# Technical Direction

## Recommended Architecture

```text
Frontend UI (React/Timeline)
        ↓
Project JSON
        ↓
PHP API / Laravel
        ↓
FFmpeg Jobs
        ↓
Generated MP4 files
```

---

# Stack Recommendation

## Backend

### Laravel

Recommended because:

- queues,
- jobs,
- storage abstraction,
- auth/team management,
- API support,
- easy admin panels,
- good FFmpeg integration.

Could later integrate:

- Filament
- Horizon
- Reverb/WebSockets

---

## Video Processing

### FFmpeg

Keep FFmpeg as the core engine.

Advantages:

- already used,
- reliable,
- scriptable,
- extremely fast for assembly tasks,
- no unnecessary abstraction.

---

### PHP-FFMpeg

Useful as a helper layer.

Can simplify:

- trims,
- metadata,
- thumbnails,
- exports.

But raw FFmpeg commands may remain preferable for advanced/custom operations.

---

# Frontend Options

## Option 1 — OpenCut

## https://opencut.dev/

### Pros

- modern UI,
- open source,
- browser-based timeline,
- already close to a usable editor.

### Cons

- heavy compared to actual needs,
- React/TypeScript ecosystem,
- may be overkill.

### Best Use

Use only the timeline/editor portion.

---

## Option 2 — OpenReel Video

## https://github.com/Augani/openreel-video

### Pros

- browser-native editing,
- modern architecture,
- potentially easier to embed.

### Cons

- still JS ecosystem,
- less mature ecosystem.

### Best Use

Interesting as a lightweight embeddable editor.

---

## Option 3 — Remotion

## https://www.remotion.dev/

### Pros

- video generation "as code",
- React-driven rendering,
- excellent for automation,
- dynamic layouts/templates.

### Cons

- not a visual editor,
- timeline UX must be built separately.

### Best Use

Very useful later for:

- branded intros,
- generated slates,
- overlays,
- dynamic title cards,
- export automation.

Probably complementary rather than replacing FFmpeg.

---

## Option 4 — Twick

## https://github.com/ncounterspecialist/twick

### Pros

- embeddable React timeline/editor,
- lightweight,
- closer to actual needs,
- reusable components.

### Cons

- requires custom integration,
- smaller ecosystem.

### Best Use

Probably one of the best candidates for this project.

Could provide:

- drag/drop timeline,
- trims,
- visual organization,
- clip management.

Without turning the app into a full NLE monster.

---

# Recommended Strategy

## Phase 1 — Minimal Viable Product

Goal:

Replace text-based assembly instructions with a visual workflow.

### Features

- upload videos,
- generate thumbnails,
- drag/drop assembly lists,
- auto-export MP4,
- review page.

### No Timeline Yet

Keep:

```text
clip A + clip B + clip C
```

Only.

---

## Phase 2 — Lightweight Editing

Add:

- trims,
- crop,
- rotate,
- optional audio controls.

Still no advanced timeline.

---

## Phase 3 — Exception Timeline

Integrate:

- Twick
or
- OpenCut components

Only for manual exceptions.

Most videos should still be automated.

---

## Phase 4 — Presentation Layer

Build a polished screening interface.

Features:

- searchable candidate list,
- instant player switching,
- preload next videos,
- notes/comments,
- sharing links,
- password-protected sessions.

---

# Data Model Suggestion

## Project JSON

```json
{
  "candidate": "John Doe",
  "clips": [
    {
      "file": "1.mov",
      "trimStart": 1.2,
      "trimEnd": 12.5,
      "crop": null
    },
    {
      "file": "2.mov"
    }
  ]
}
```

---

# Important Design Philosophy

## Do NOT Build a Full Premiere Clone

That path becomes:

- extremely complex,
- expensive,
- fragile,
- hard to maintain.

Your real workflow is:

```text
automation first
manual corrections second
```

That is a much smarter target.

---

# Final Recommendation

## Most Realistic Stack

### Backend

- Laravel
- FFmpeg
- queues/jobs

### Frontend

- React
- Twick (preferred lightweight editor)
or
- selected OpenCut/OpenReel components

### Rendering

- FFmpeg for final exports
- optional Remotion later for generated graphics/templates

---

# Suggested Immediate Next Step

Build only:

1. upload management,
2. candidate assembly builder,
3. auto-export pipeline,
4. review page.

That alone would already remove most manual friction from the workflow.


---

# Additional Important Feature

## Direct Browser Recording

A very valuable addition would be allowing actors to record directly from the web application.

This would solve several existing problems:

- inconsistent formats,
- wrong aspect ratios,
- upload friction,
- external transfer tools,
- missing files,
- codec incompatibilities.

---

## Basic Workflow

```text
Actor receives casting link
        ↓
Opens web app
        ↓
Records directly in browser
        ↓
Preview / retry
        ↓
Automatic upload
        ↓
Immediately available in project
```

---

# Technical Benefits

## Standardized Media

Huge advantage:

the platform can enforce:

- aspect ratio,
- resolution,
- framerate,
- codec,
- bitrate,
- orientation.

This dramatically simplifies the assembly pipeline.

---

## Instant Availability

No more:

- WeTransfer delays,
- manual imports,
- SFTP transfers,
- missing downloads.

Videos appear instantly in the platform.

---

## Remote Casting Potential

This also opens the door to a future "live remote casting" mode.

Possible future workflow:

```text
Director / Casting Director joins session
        ↓
Actor connects from browser
        ↓
Live guidance during audition
        ↓
Recording stored instantly
        ↓
Automatic assembly/review
```

Potential future features:

- live video direction,
- synchronized playback,
- session recording,
- real-time notes,
- multi-user review,
- remote callbacks.

This could become a very strong differentiator.

---

# Recommended Technologies for Browser Recording

## Short-Term Simple Approach

### MediaRecorder API

Browser-native recording API.

Advantages:

- simple,
- no plugin,
- supported everywhere modern,
- low complexity.

Workflow:

```text
Browser Camera
        ↓
MediaRecorder
        ↓
Upload chunks
        ↓
Server-side FFmpeg normalization
```

Very suitable for MVP.

---

# Future Live Collaboration Stack

## WebRTC

For later real-time casting sessions.

Would allow:

- low-latency video/audio,
- live supervision,
- remote direction.

Possible stack:

- WebRTC
- Laravel Reverb/WebSockets
- TURN/STUN servers
- FFmpeg recording pipeline

---

# Strategic Observation

This project is evolving toward something larger than a simple assembly tool.

Potential long-term positioning:

```text
Self-hosted casting workflow platform
```

Including:

- submissions,
- recording,
- review,
- assembly,
- remote casting,
- collaboration,
- delivery.

That is a much more unique niche than "yet another web video editor".
