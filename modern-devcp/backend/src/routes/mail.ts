import { Router } from 'express'

const router = Router()

// GET /api/mail
router.get('/', (req, res) => {
  res.json({
    message: 'Mail Account endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/mail
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'Mail Account created successfully',
    data: req.body,
  })
})

// GET /api/mail/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'Mail Account details',
    data: { id: req.params.id },
  })
})

// PUT /api/mail/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'Mail Account updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/mail/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'Mail Account deleted successfully',
    id: req.params.id,
  })
})

export default router
