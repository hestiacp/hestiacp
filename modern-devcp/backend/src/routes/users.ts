import { Router } from 'express'

const router = Router()

// GET /api/users
router.get('/', (req, res) => {
  res.json({
    message: 'User endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/users
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'User created successfully',
    data: req.body,
  })
})

// GET /api/users/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'User details',
    data: { id: req.params.id },
  })
})

// PUT /api/users/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'User updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/users/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'User deleted successfully',
    id: req.params.id,
  })
})

export default router
